# *curlgen* - a tool to generate code for php curl ###
## usage
php curlgen.php [raw\_request\_file]

PS: raw\_request\_file is raw request of fiddle or whistle.

## demo
*RUN*: php curlgen.php sohu_demo.txt

### sohu_demo.txt (from whistle's request raw)
```
GET /20170511/n492649748.shtml?fi HTTP/1.1
host: news.sohu.com
cache-control: max-age=0
upgrade-insecure-requests: 1
user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.96 Safari/537.36
accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
referer: http://www.sohu.com/
accept-encoding: gzip
accept-language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4
if-modified-since: Thu, 11 May 2017 11:12:46 GMT
connection: keep-alive
```

### program output
```
// return [header, body]
function httpReq() {
  $url = "http://news.sohu.com/20170511/n492649748.shtml?fi";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $url);
  $headers = [
    'host: news.sohu.com',
    'cache-control: max-age=0',
    'upgrade-insecure-requests: 1',
    'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.96 Safari/537.36',
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'referer: http://www.sohu.com/',
    'accept-language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
    'if-modified-since: Thu, 11 May 2017 11:12:46 GMT',
    'connection: close',
  ];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $resp = curl_exec($ch);
  curl_close($ch);
  return explode("\r\n\r\n", $resp, 2);
}
```

then you can use the code for curl programming.