<?php

//simple gen curl 
class CurlGen {

    private function getReqHeaderBody($reqRaw) {
        $split = "";
        if (strpos($reqRaw, "\r\n\r\n") !== false) {
            $split = "\r\n\r\n";
        } else if (strpos($reqRaw, "\n\n") !== false) { //maybe \r not exists
            $split = "\n\n";
        }
        if ($split) {
            return explode($split, $reqRaw, 2);
        } 
        return [$reqRaw, ""];
    }

    public function build($reqRaw) {
        if (!$reqRaw) {
            return false;
        }
        $conf = [];
        list($reqHeader, $reqData) = $this->getReqHeaderBody($reqRaw);
        $lines = explode("\n", $reqHeader);
        $headStr = trim($lines[0]);
        list($method, $path, $ver) = explode(" ", $headStr);
        $conf['method'] = $method;
        $conf['path'] = $path;
        $conf['req_data'] = $reqData;
        $conf['headers'] = [];
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            $line = $this->processHeader($line);
            if ($line) {
                $conf['headers'][] = $line;
                if (stripos($line, 'host:') === 0) {
                    $conf['host'] = trim(substr($line, 5));
                }
            }
        }
        return $this->buildCode($conf);
    }

    private function processHeader($line) {
        if (stripos($line, 'connection: keep-alive') === 0) {
            return 'connection: close';
        }
        if (stripos($line, 'accept-encoding: gzip') === 0) {
            return '';
        }
        return $line;
    }

    private function buildCode($conf) {
        $codes = [];
        $url = "http://" . $conf['host'] . $conf['path'];
        $codes[] = sprintf('$url = "%s";', $url);
        $codes[] = '$ch = curl_init();';
        $codes[] = 'curl_setopt($ch, CURLOPT_HEADER, true);';
        $codes[] = 'curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);';
        $codes[] = 'curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);';  
        $codes[] = 'curl_setopt($ch, CURLOPT_URL, $url);';
        if ($conf['method'] == 'POST') {
            $codes[] = sprintf('$postData = "%s";', $conf['req_data']);
            $codes[] = 'curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);';
        }
        $codes = array_merge($codes, $this->buildHeaderCode($conf['headers']));
        $codes[] = 'curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);';
        $codes[] = '$resp = curl_exec($ch);';
        $codes[] = 'curl_close($ch);';
        $codes[] = 'return explode("\r\n\r\n", $resp, 2);';
        return $this->buildFuncCode($codes);
    }

    private function buildFuncCode($codes) {
        $fcodes = [];
        $fcodes[] = 'function httpReq() {';
        foreach ($codes as $code) {
            $fcodes[] = "  " . $code;
        }
        $fcodes[] = "}";
        return implode("\n", $fcodes) . "\n";
    }

    private function buildHeaderCode($headers) {
        $codes = [];
        $codes[] = '$headers = [';
        foreach ($headers as $header) {
            $codes[] = sprintf("  '%s',", $header);
        }
        $codes[] = "];";
        return $codes;
    }

}


if ($argc < 2) {
    die("need req raw file!\n");
}
$file = $argv[1];
$gen = new CurlGen();
$reqRaw = file_get_contents($file);
echo $gen->build($reqRaw);