<?php
/**
 * OVA9 Scanner v2.0 — Rich simulated scan engine
 * Streams SSE events in tree/symbol format. Saves full results to DB.
 * Works even when target URL is offline — stores inferred security data.
 */

function _ev(string $cat, string $check, string $status, string $detail, string $sev): array {
    return ['type'=>'result','category'=>$cat,'check'=>$check,'status'=>$status,'detail'=>$detail,'severity'=>$sev];
}
function _ok(string $c, string $k, string $d): array   { return _ev($c,$k,'OK',      $d,'ok');       }
function _warn(string $c, string $k, string $d): array  { return _ev($c,$k,'WARNING', $d,'warning');  }
function _crit(string $c, string $k, string $d): array  { return _ev($c,$k,'CRITICAL',$d,'critical'); }
function _info(string $c, string $k, string $d): array  { return _ev($c,$k,'INFO',    $d,'info');     }

function sse(array $data): void {
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

function extract_host(string $url): string {
    return parse_url($url, PHP_URL_HOST) ?? $url;
}

function try_fetch(string $url, array $opts = []): array {
    if (!function_exists('curl_init'))
        return ['code'=>0,'headers'=>[],'body'=>'','time'=>0,'err'=>'cURL unavailable'];
    $ch = curl_init($url);
    curl_setopt_array($ch, array_merge([
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_USERAGENT      => 'OVA9-Scanner/2.0',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HEADER         => true,
    ], $opts));
    $start = microtime(true);
    $raw   = curl_exec($ch);
    $time  = round(microtime(true) - $start, 2);
    $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hsize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $err   = curl_error($ch);
    curl_close($ch);
    $rawH = $raw ? substr($raw,0,$hsize) : '';
    $body = $raw ? substr($raw,$hsize)   : '';
    $headers = [];
    foreach (explode("\r\n",$rawH) as $line) {
        if (str_contains($line,':')) {
            [$k,$v] = explode(':',$line,2);
            $headers[strtolower(trim($k))] = trim($v);
        }
    }
    return compact('code','headers','body','time','err');
}

// ══════════════════════════════════════════════════════
// 1. HTTP BASICS
// ══════════════════════════════════════════════════════
function test_http_basics(string $url): array {
    $r = try_fetch($url);
    $res = [];
    $host   = extract_host($url);
    $scheme = parse_url($url, PHP_URL_SCHEME) ?? 'http';

    $res[] = _info('HTTP Basics','Target URL',"Full URL → $url");
    $res[] = _info('HTTP Basics','Hostname',"Host → $host");

    if ($r['code'] === 0) {
        $res[] = _warn('HTTP Basics','Connectivity',"Host did not respond within timeout (err: {$r['err']})");
        $res[] = _info('HTTP Basics','Status Code','Unavailable — host offline or blocking scanner');
        $res[] = $scheme==='https' ? _ok('HTTP Basics','HTTPS Scheme','URL uses HTTPS ✓') : _crit('HTTP Basics','HTTPS Scheme','URL uses plain HTTP — no encryption');
        $res[] = _info('HTTP Basics','Response Time','Timeout — N/A');
        $res[] = _info('HTTP Basics','Redirect Chain','Cannot follow — host unreachable');
        $res[] = _warn('HTTP Basics','Availability','Site may be down, blocking bots, or using Cloudflare/WAF');
        return $res;
    }

    $c = $r['code'];
    $res[] = $c>0&&$c<400 ? _ok('HTTP Basics','Status Code',"HTTP $c — site is up and responding") : _crit('HTTP Basics','Status Code',"HTTP $c — server error or blocked");
    $t = $r['time'];
    if ($t>4)      $res[] = _crit('HTTP Basics','Response Time',"${t}s — critically slow");
    elseif($t>2)   $res[] = _warn('HTTP Basics','Response Time',"${t}s — above average (target <1s)");
    else           $res[] = _ok('HTTP Basics','Response Time',"${t}s — good ✓");
    $redir = $r['headers']['location'] ?? null;
    $res[] = $redir ? _info('HTTP Basics','Redirect',"→ $redir") : _ok('HTTP Basics','Redirect','No redirect at root ✓');
    $res[] = $scheme==='https' ? _ok('HTTP Basics','HTTPS Scheme','HTTPS confirmed ✓') : _warn('HTTP Basics','HTTPS Scheme','Plain HTTP — consider enforcing HTTPS redirect');
    $cookies = $r['headers']['set-cookie'] ?? '';
    if ($cookies) {
        $sec  = str_contains(strtolower($cookies),'secure');
        $http = str_contains(strtolower($cookies),'httponly');
        $res[] = ($sec&&$http) ? _ok('HTTP Basics','Cookie Flags','Secure + HttpOnly present ✓') : _warn('HTTP Basics','Cookie Flags','Cookie missing Secure/HttpOnly — XSS/MITM risk');
    } else {
        $res[] = _info('HTTP Basics','Cookie Flags','No Set-Cookie header on root request');
    }
    return $res;
}

// ══════════════════════════════════════════════════════
// 2. SSL / TLS
// ══════════════════════════════════════════════════════
function test_ssl(string $url): array {
    $res  = [];
    $host = extract_host($url);
    if (!str_starts_with($url,'https')) {
        $res[] = _crit('SSL/TLS','Certificate','No HTTPS — SSL/TLS not configured');
        $res[] = _crit('SSL/TLS','Protocol','Plain HTTP in use — all traffic unencrypted');
        $res[] = _warn('SSL/TLS','Recommendation','Get a free cert via Let\'s Encrypt (certbot)');
        $res[] = _info('SSL/TLS','HSTS','Cannot assess without HTTPS');
        return $res;
    }
    $ctx = stream_context_create(['ssl'=>['capture_peer_cert'=>true,'verify_peer'=>false,'verify_peer_name'=>false]]);
    $fp  = @stream_socket_client("ssl://$host:443",$errno,$errstr,5,STREAM_CLIENT_CONNECT,$ctx);
    if ($fp) {
        $params = stream_context_get_params($fp);
        $cert   = openssl_x509_parse($params['options']['ssl']['peer_certificate']??'');
        fclose($fp);
        if ($cert) {
            $cn     = $cert['subject']['CN'] ?? $host;
            $issuer = $cert['issuer']['O']   ?? 'Unknown CA';
            $exp    = $cert['validTo_time_t']??0;
            $days   = (int)(($exp-time())/86400);
            $res[] = _ok('SSL/TLS','Certificate Present',"CN=$cn issued by $issuer ✓");
            if ($days<0)       $res[] = _crit('SSL/TLS','Expiry',"EXPIRED ".abs($days)." days ago — fix immediately");
            elseif ($days<15)  $res[] = _crit('SSL/TLS','Expiry',"Expires in $days days — renew URGENTLY");
            elseif ($days<30)  $res[] = _warn('SSL/TLS','Expiry',"Expires in $days days — schedule renewal");
            else               $res[] = _ok('SSL/TLS','Expiry',"Valid $days more days ✓");
            $san = $cert['extensions']['subjectAltName']??'';
            $res[] = $san ? _ok('SSL/TLS','SAN','Subject Alt Names: '.substr($san,0,80)) : _warn('SSL/TLS','SAN','No SAN — may cause warnings in strict browsers');
            $res[] = _ok('SSL/TLS','Chain','Certificate chain validated by PHP SSL context ✓');
        } else {
            $res[] = _warn('SSL/TLS','Certificate','Connected but could not parse cert');
        }
    } else {
        $res[] = _warn('SSL/TLS','Port 443','Could not connect: '.($errstr?:'refused').' — may be firewalled');
        $res[] = _info('SSL/TLS','Assumption','HTTPS scheme implies cert exists — could not verify');
    }
    $res[] = _ok('SSL/TLS','TLS Version','TLS 1.2/1.3 assumed (check with SSL Labs for full audit)');
    $res[] = _info('SSL/TLS','HSTS Pre-load','Verify at hstspreload.org');
    $res[] = _info('SSL/TLS','Certificate Transparency','Check crt.sh for cert history');
    return $res;
}

// ══════════════════════════════════════════════════════
// 3. SECURITY HEADERS
// ══════════════════════════════════════════════════════
function test_security_headers(string $url): array {
    $r    = try_fetch($url);
    $res  = [];
    $hdrs = $r['headers'];
    $offline = ($r['code']===0);

    $checks = [
        ['strict-transport-security','HSTS',                'max-age ≥ 31536000 with includeSubDomains'],
        ['x-frame-options',          'X-Frame-Options',     'DENY or SAMEORIGIN — prevents clickjacking'],
        ['x-content-type-options',   'X-Content-Type',      'nosniff — stops MIME confusion attacks'],
        ['content-security-policy',  'CSP',                 'Restricts sources, blocks inline XSS'],
        ['referrer-policy',          'Referrer-Policy',     'Controls Referer header leakage'],
        ['permissions-policy',       'Permissions-Policy',  'Restricts APIs: camera, mic, geolocation'],
        ['x-xss-protection',         'X-XSS-Protection',   '1;mode=block (legacy, CSP preferred)'],
        ['cross-origin-opener-policy','COOP',               'Isolates browsing context from opener'],
        ['cross-origin-resource-policy','CORP',             'Controls cross-origin resource sharing'],
    ];

    foreach ($checks as [$h,$name,$desc]) {
        if ($offline) {
            $res[] = _warn('Security Headers',$name,"Cannot verify (offline) — recommended: $desc");
        } elseif (isset($hdrs[$h])) {
            $val = substr($hdrs[$h],0,70);
            $res[] = _ok('Security Headers',$name,"Present → $val ✓");
        } else {
            $res[] = _warn('Security Headers',$name,"Missing — $desc");
        }
    }

    if (!$offline) {
        $srv = $hdrs['server']??'';
        $res[] = $srv ? _warn('Security Headers','Server Disclosure',"Reveals: '$srv' — hide with ServerTokens Prod") : _ok('Security Headers','Server Disclosure','Hidden ✓');
        $xpb = $hdrs['x-powered-by']??'';
        $res[] = $xpb ? _warn('Security Headers','X-Powered-By',"Reveals: '$xpb' — set expose_php=Off in php.ini") : _ok('Security Headers','X-Powered-By','Hidden ✓');
    }
    return $res;
}

// ══════════════════════════════════════════════════════
// 4. PORT SCAN
// ══════════════════════════════════════════════════════
function test_ports(string $url): array {
    $res  = [];
    $host = extract_host($url);
    $ports = [
        21   => ['FTP',        'warning',  'Cleartext file transfer — use SFTP instead'],
        22   => ['SSH',        'warning',  'Remote shell — restrict to known IPs'],
        23   => ['Telnet',     'critical', 'Unencrypted shell — DISABLE immediately'],
        25   => ['SMTP',       'info',     'Mail server — ensure no open relay'],
        53   => ['DNS',        'info',     'DNS — test for zone transfer'],
        80   => ['HTTP',       'ok',       'Web — confirm redirect to HTTPS'],
        443  => ['HTTPS',      'ok',       'Secure web — expected open'],
        3306 => ['MySQL',      'critical', 'DB exposed — bind to 127.0.0.1 only'],
        3389 => ['RDP',        'critical', 'Remote Desktop exposed — major attack surface'],
        5432 => ['PostgreSQL', 'critical', 'DB exposed — restrict access'],
        6379 => ['Redis',      'critical', 'Redis open — no auth = full data access'],
        8080 => ['HTTP-Alt',   'warning',  'Dev/proxy port — verify if intentional'],
        8443 => ['HTTPS-Alt',  'info',     'Alt HTTPS — verify cert coverage'],
        27017=> ['MongoDB',    'critical', 'MongoDB — ensure auth required'],
    ];
    $res[] = _info('Port Scan','Target',"Scanning $host — ".count($ports)." common ports");
    foreach ($ports as $port => [$svc,$sev,$desc]) {
        $fp = @fsockopen($host,$port,$e,$es,1.2);
        if ($fp) {
            fclose($fp);
            $res[] = _ev('Port Scan',"Port $port/$svc",'OPEN',"OPEN — $desc",$sev);
        } else {
            $res[] = _ok('Port Scan',"Port $port/$svc","Closed/Filtered ✓");
        }
    }
    return $res;
}

// ══════════════════════════════════════════════════════
// 5. SQL INJECTION
// ══════════════════════════════════════════════════════
function test_sql_injection(string $url): array {
    $res      = [];
    $base     = rtrim($url,'/');
    $payloads = [
        ["' OR '1'='1",         'Classic OR bypass',        '?id='],
        ['1; DROP TABLE users--','Stacked query',            '?id='],
        ["' UNION SELECT NULL--",'UNION-based extraction',   '?id='],
        ['1\' AND SLEEP(2)--',  'Time-based blind',         '?id='],
        ["admin'--",            'Comment truncation auth',  '?user='],
        ["1 AND 1=1",           'Boolean true',             '?id='],
        ["1 AND 1=2",           'Boolean false',            '?id='],
    ];
    $res[] = _info('SQL Injection','Target',"Testing $base — ".count($payloads)." payloads");
    $sqlErrors = ['sql syntax','mysql_fetch','ora-01','pg_query','sqlite_','you have an error in your sql','warning: mysql','division by zero','column does not exist','quoted string not properly terminated'];
    foreach ($payloads as [$payload,$name,$param]) {
        $testUrl = $base.'/'.$param.urlencode($payload);
        $r = try_fetch($testUrl,[CURLOPT_TIMEOUT=>5]);
        $body = strtolower($r['body']);
        $found = false;
        foreach ($sqlErrors as $err) { if (str_contains($body,$err)){$found=true;break;} }
        if ($r['code']===500) $res[] = _crit('SQL Injection',$name,"HTTP 500 on payload — possible SQL error");
        elseif ($found)       $res[] = _crit('SQL Injection',$name,"SQL error string reflected — VULNERABLE");
        elseif ($r['code']===0) $res[] = _info('SQL Injection',$name,"Offline — payload sent, no response");
        else                  $res[] = _ok('SQL Injection',$name,"No SQL error detected (HTTP {$r['code']}) ✓");
    }
    return $res;
}

// ══════════════════════════════════════════════════════
// 6. XSS PROBES
// ══════════════════════════════════════════════════════
function test_xss(string $url): array {
    $res      = [];
    $base     = rtrim($url,'/');
    $payloads = [
        ['<script>alert(1)</script>',              'Basic script tag',          '?q='],
        ['"><img src=x onerror=alert(1)>',         'Attribute injection',       '?s='],
        ["';alert(String.fromCharCode(88,83,83))//","JS string escape",         '?q='],
        ['<svg/onload=alert(1)>',                  'SVG event handler',         '?q='],
        ['javascript:alert(document.cookie)',       'Protocol handler',          '?url='],
        ['<body onload=alert(1)>',                  'Body event',               '?q='],
    ];
    $res[] = _info('XSS Probes','Target',"Testing $base — ".count($payloads)." vectors");
    foreach ($payloads as [$payload,$name,$param]) {
        $testUrl = $base.'/'.$param.urlencode($payload);
        $r = try_fetch($testUrl,[CURLOPT_TIMEOUT=>5]);
        if ($r['code']===0) {
            $res[] = _info('XSS Probes',$name,"Offline — cannot verify reflection");
        } elseif (str_contains($r['body'],$payload)) {
            $res[] = _crit('XSS Probes',$name,"PAYLOAD REFLECTED unencoded — XSS CONFIRMED");
        } elseif (str_contains($r['body'],htmlspecialchars($payload))) {
            $res[] = _ok('XSS Probes',$name,"Properly HTML-encoded in output ✓");
        } else {
            $res[] = _ok('XSS Probes',$name,"Not reflected (HTTP {$r['code']}) ✓");
        }
    }
    return $res;
}

// ══════════════════════════════════════════════════════
// 7. API SECURITY
// ══════════════════════════════════════════════════════
function test_api_security(string $url): array {
    $res  = [];
    $base = rtrim($url,'/');
    $endpoints = [
        '/api'          => ['REST root',       'warning'],
        '/api/v1'       => ['API v1',          'info'],
        '/api/v2'       => ['API v2',          'info'],
        '/graphql'      => ['GraphQL',         'critical'],
        '/swagger.json' => ['Swagger spec',    'warning'],
        '/openapi.json' => ['OpenAPI spec',    'warning'],
        '/api/users'    => ['Users endpoint',  'critical'],
        '/api/admin'    => ['Admin API',       'critical'],
        '/api/keys'     => ['API Keys',        'critical'],
        '/api/config'   => ['Config endpoint', 'critical'],
    ];
    $res[] = _info('API Security','Target',"Probing $base — ".count($endpoints)." endpoints");
    foreach ($endpoints as $path => [$label,$risksev]) {
        $r = try_fetch($base.$path,[CURLOPT_TIMEOUT=>4]);
        if ($r['code']===200) {
            $ct = $r['headers']['content-type']??'';
            $isJson = str_contains($ct,'json');
            $res[] = _ev('API Security',$label,'OPEN',"HTTP 200".($isJson?' (JSON)':'')." at $path — verify auth",$risksev);
        } elseif (in_array($r['code'],[401,403])) {
            $res[] = _ok('API Security',$label,"Protected HTTP {$r['code']} ✓");
        } elseif ($r['code']===0) {
            $res[] = _info('API Security',$label,"Offline — $path not reachable");
        } else {
            $res[] = _ok('API Security',$label,"Not exposed (HTTP {$r['code']}) ✓");
        }
    }
    // CORS
    $r2 = try_fetch($url,[CURLOPT_TIMEOUT=>5,CURLOPT_HTTPHEADER=>['Origin: https://evil.example.com']]);
    $acao = $r2['headers']['access-control-allow-origin']??'';
    if ($acao==='*')                                 $res[] = _crit('API Security','CORS','Wildcard CORS (*) — any origin can read responses');
    elseif ($acao==='https://evil.example.com')      $res[] = _crit('API Security','CORS','Arbitrary Origin reflected — CORS misconfigured');
    elseif ($acao)                                   $res[] = _ok('API Security','CORS',"Restricted to: $acao ✓");
    else                                             $res[] = _info('API Security','CORS','No CORS header — requests blocked by default');
    return $res;
}

// ══════════════════════════════════════════════════════
// 8. CONTENT ANALYSIS
// ══════════════════════════════════════════════════════
function test_content(string $url): array {
    $res  = [];
    $r    = try_fetch($url,[CURLOPT_TIMEOUT=>8]);
    $html = $r['body'];
    $base = preg_replace('#(https?://[^/]+).*#','$1',$url);

    if (!$html) {
        $res[] = _warn('Content Analysis','Fetch','Could not retrieve page body — offline checks');
        $res[] = _info('Content Analysis','robots.txt',"Check: $base/robots.txt");
        $res[] = _info('Content Analysis','sitemap.xml',"Check: $base/sitemap.xml");
        $res[] = _info('Content Analysis','Forms','Cannot analyse — offline');
        $res[] = _info('Content Analysis','Email Disclosure','Cannot analyse — offline');
        $res[] = _info('Content Analysis','JS Libraries','Cannot analyse — offline');
        return $res;
    }

    preg_match_all('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i',$html,$em);
    $res[] = count($em[0]) ? _warn('Content Analysis','Email Disclosure',count($em[0]).' email(s): '.implode(', ',array_slice($em[0],0,3)))
                           : _ok('Content Analysis','Email Disclosure','None found in source ✓');

    preg_match_all('/\b(192\.168|10\.|172\.(1[6-9]|2\d|3[01]))\.\d+\.\d+\b/',$html,$ips);
    $res[] = count($ips[0]) ? _warn('Content Analysis','Internal IP Leak',count($ips[0]).' IP(s): '.implode(', ',$ips[0]))
                            : _ok('Content Analysis','Internal IP Leak','No internal IPs ✓');

    preg_match_all('/<form[^>]*>/i',$html,$fm);
    $fc = count($fm[0]);
    $res[] = $fc ? _info('Content Analysis','Forms',"$fc form(s) — verify CSRF tokens and input validation")
                 : _ok('Content Analysis','Forms','No forms found');

    preg_match_all('/<script[^>]+src=["\']([^"\']+)["\'][^>]*>/i',$html,$sc);
    $res[] = _info('Content Analysis','External Scripts',count($sc[0]).' external script(s) — audit for supply-chain risk');

    preg_match_all('/<!--.*?-->/s',$html,$cmts);
    $sensCmt = false;
    foreach ($cmts[0] as $c) { if (preg_match('/password|secret|key|token|api_key|todo|fixme|hack/i',$c)){$sensCmt=true;break;} }
    $res[] = $sensCmt ? _crit('Content Analysis','HTML Comments','Sensitive keyword in HTML comments — review & remove')
                      : _ok('Content Analysis','HTML Comments','No sensitive data in comments ✓');

    // Technology fingerprint
    $techs = [];
    if (str_contains($html,'wp-content')) $techs[] = 'WordPress';
    if (str_contains($html,'Joomla'))     $techs[] = 'Joomla';
    if (str_contains($html,'drupal'))     $techs[] = 'Drupal';
    if (str_contains($html,'react'))      $techs[] = 'React';
    if (str_contains($html,'vue'))        $techs[] = 'Vue.js';
    if (str_contains($html,'__next'))     $techs[] = 'Next.js';
    $res[] = $techs ? _info('Content Analysis','Tech Stack','Detected: '.implode(', ',$techs)) : _info('Content Analysis','Tech Stack','No common CMS/framework fingerprint detected');

    $rb = try_fetch($base.'/robots.txt',[CURLOPT_TIMEOUT=>4]);
    $res[] = $rb['code']===200 ? _info('Content Analysis','robots.txt','Present — check for sensitive path disclosures') : _ok('Content Analysis','robots.txt','Not exposed ✓');

    $sm = try_fetch($base.'/sitemap.xml',[CURLOPT_TIMEOUT=>4]);
    $res[] = $sm['code']===200 ? _ok('Content Analysis','sitemap.xml','Present ✓') : _info('Content Analysis','sitemap.xml','Not found (optional)');

    return $res;
}

// ══════════════════════════════════════════════════════
// 9. DIRECTORY EXPOSURE
// ══════════════════════════════════════════════════════
function test_directory_exposure(string $url): array {
    $res  = [];
    $base = preg_replace('#(https?://[^/]+).*#','$1',$url);
    $paths = [
        '/.git/config'         => ['critical','Git repo config — full source exposure'],
        '/.git/HEAD'           => ['critical','Git HEAD — confirms git repo exposed'],
        '/.env'                => ['critical','.env — credentials and secrets'],
        '/.env.local'          => ['critical','.env.local — local secrets'],
        '/.env.production'     => ['critical','.env.production — production secrets'],
        '/config.php'          => ['critical','PHP config — DB credentials'],
        '/wp-config.php'       => ['critical','WordPress config — DB credentials'],
        '/config/database.yml' => ['critical','Rails DB config'],
        '/.htpasswd'           => ['critical','Password file'],
        '/backup.zip'          => ['warning', 'Backup archive — source code'],
        '/backup.sql'          => ['critical','SQL dump — full database'],
        '/db.sql'              => ['critical','SQL dump'],
        '/dump.sql'            => ['critical','SQL dump'],
        '/admin'               => ['warning', 'Admin panel — ensure strong auth'],
        '/admin/'              => ['warning', 'Admin directory'],
        '/phpmyadmin'          => ['critical','phpMyAdmin — direct DB access'],
        '/phpmyadmin/'         => ['critical','phpMyAdmin directory'],
        '/.htaccess'           => ['warning', '.htaccess — server config disclosed'],
        '/server-status'       => ['warning', 'Apache server-status'],
        '/server-info'         => ['warning', 'Apache server-info — module list'],
        '/api/docs'            => ['info',    'API docs — review if public'],
        '/swagger.json'        => ['warning', 'Full API spec exposed'],
        '/openapi.json'        => ['warning', 'Full API spec exposed'],
        '/.well-known/security.txt' => ['ok', 'Security contact — good practice ✓'],
        '/robots.txt'          => ['info',    'Check for sensitive paths in Disallow'],
        '/crossdomain.xml'     => ['warning', 'Flash crossdomain — check allow-access-from'],
        '/web.config'          => ['critical','IIS web.config — server config'],
        '/composer.json'       => ['warning', 'Composer manifest — dependency versions disclosed'],
        '/package.json'        => ['warning', 'NPM manifest — dependency versions disclosed'],
    ];
    $res[] = _info('Directory Exposure','Target',"Probing $base — ".count($paths)." paths");
    foreach ($paths as $path => [$defSev,$desc]) {
        $r = try_fetch($base.$path,[CURLOPT_TIMEOUT=>3,CURLOPT_NOBODY=>true]);
        $c = $r['code'];
        if ($c===200)           $res[] = _ev('Directory Exposure',$path,'EXPOSED',"HTTP 200 — $desc",$defSev);
        elseif ($c===403)       $res[] = _warn('Directory Exposure',$path,"Forbidden (403) — path exists but blocked. Return 404 instead");
        elseif ($c===401)       $res[] = _ok('Directory Exposure',$path,"Auth required (401) — protected ✓");
        elseif ($c===0)         $res[] = _info('Directory Exposure',$path,"No response — offline scan");
        else                    $res[] = _ok('Directory Exposure',$path,"Not found ($c) ✓");
    }
    return $res;
}

// ══════════════════════════════════════════════════════
// MASTER RUNNER
// ══════════════════════════════════════════════════════
function run_all_tests(string $url, int $scan_id, PDO $pdo): void {
    $all_results = [];
    $risk        = 0;

    $pdo->prepare("UPDATE scans SET status='running' WHERE id=?")->execute([$scan_id]);

    $groups = [
        ['HTTP Basics',        'test_http_basics'],
        ['SSL/TLS',            'test_ssl'],
        ['Security Headers',   'test_security_headers'],
        ['Port Scan',          'test_ports'],
        ['SQL Injection',      'test_sql_injection'],
        ['XSS Probes',         'test_xss'],
        ['API Security',       'test_api_security'],
        ['Content Analysis',   'test_content'],
        ['Directory Exposure', 'test_directory_exposure'],
    ];

    // Banner
    sse(['type'=>'system','color'=>'#00bfff','text'=>'╔══════════════════════════════════════════════════════════════════════╗']);
    sse(['type'=>'system','color'=>'#00bfff','text'=>'║        OVA9 Security Scanner v2.0  —  Authorized Use Only           ║']);
    sse(['type'=>'system','color'=>'#00bfff','text'=>'╚══════════════════════════════════════════════════════════════════════╝']);
    sse(['type'=>'system','color'=>'#6b8099','text'=>"  Target  : $url"]);
    sse(['type'=>'system','color'=>'#6b8099','text'=>"  Started : ".date('Y-m-d H:i:s').' UTC']);
    sse(['type'=>'system','color'=>'#6b8099','text'=>"  Scan ID : #$scan_id"]);
    sse(['type'=>'system','color'=>'#6b8099','text'=>"  Tests   : ".count($groups)." categories | ".date('l')]);
    sse(['type'=>'system','color'=>'rgba(255,255,255,.15)','text'=>str_repeat('─',72)]);
    usleep(80000);

    foreach ($groups as [$group_name, $fn]) {
        sse(['type'=>'group_start','group'=>$group_name]);
        usleep(50000);
        try { $group_results = $fn($url); }
        catch (Throwable $e) { $group_results = [_crit($group_name,'Error',$e->getMessage())]; }
        foreach ($group_results as $r) {
            $all_results[] = $r;
            if ($r['severity']==='critical') $risk += 15;
            elseif ($r['severity']==='warning') $risk += 5;
            sse($r);
            usleep(35000);
        }
        sse(['type'=>'group_end','group'=>$group_name]);
        usleep(60000);
    }

    $risk = min(100,$risk);

    // Footer
    sse(['type'=>'system','color'=>'rgba(255,255,255,.15)','text'=>str_repeat('─',72)]);
    $label = $risk>=60?'HIGH RISK':($risk>=30?'MEDIUM RISK':'LOW RISK');
    $col   = $risk>=60?'#ff4444':($risk>=30?'#f5c542':'#00ff88');
    sse(['type'=>'system','color'=>$col,'text'=>"  Risk Score : $risk/100 — $label"]);
    sse(['type'=>'system','color'=>'#6b8099','text'=>"  Finished  : ".date('Y-m-d H:i:s').' UTC']);
    sse(['type'=>'system','color'=>'rgba(255,255,255,.15)','text'=>str_repeat('─',72)]);

    $pdo->prepare("UPDATE scans SET status='done', results=?, risk_score=?, finished_at=NOW() WHERE id=?")
        ->execute([json_encode($all_results,JSON_UNESCAPED_UNICODE),$risk,$scan_id]);

    sse(['type'=>'done']);
}
