--TEST--
OAuth plaintext access token
--SKIPIF--
<?php
require 'skip.inc';
require 'server.inc';
http_server_skipif('tcp://127.0.0.1:12342');
?>
--FILE--
<?php
require 'server.inc';

$x = new OAuth('conskey', 'conssecret', OAUTH_SIG_METHOD_PLAINTEXT);
$x->setRequestEngine(OAUTH_REQENGINE_STREAMS);
$x->setTimestamp(12345);
$x->setNonce('testing');

$pid = http_server("tcp://127.0.0.1:12342", array(
	"HTTP/1.0 200 OK\r\nContent-Type: text/plain\r\nContent-Length: 40\r\n\r\noauth_token=1234&oauth_token_secret=4567",
	"HTTP/1.0 200 OK\r\nContent-Type: text/plain\r\nContent-Length: 40\r\n\r\noauth_token=4567&oauth_token_secret=8901",
), $output);

$x->setAuthType(OAUTH_AUTH_TYPE_URI);
$x->setToken("key", "secret");
var_dump($x->getAccessToken('http://127.0.0.1:12342/test'));
var_dump($x->getAccessToken('http://127.0.0.1:12342/test', '', '', 'GET'));

fseek($output, 0, SEEK_SET);
var_dump(stream_get_contents($output));

http_server_kill($pid);

?>
--EXPECTF--
array(2) {
  ["oauth_token"]=>
  string(4) "1234"
  ["oauth_token_secret"]=>
  string(4) "4567"
}
array(2) {
  ["oauth_token"]=>
  string(4) "4567"
  ["oauth_token_secret"]=>
  string(4) "8901"
}
string(%d) "POST /test?oauth_consumer_key=conskey&oauth_signature_method=PLAINTEXT&oauth_nonce=testing&oauth_timestamp=12345&oauth_version=1.0&oauth_token=key&oauth_signature=conssecret%26secret HTTP/1.%d
Host: 127.0.0.1:12342
Connection: close

GET /test?oauth_consumer_key=conskey&oauth_signature_method=PLAINTEXT&oauth_nonce=testing&oauth_timestamp=12345&oauth_version=1.0&oauth_token=key&oauth_signature=conssecret%26secret HTTP/1.%d
Host: 127.0.0.1:12342
Connection: close

"
