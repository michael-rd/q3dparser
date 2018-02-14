# q3dparser
q3-demo file parser

see test/Q3DemoParserTest.php for usage examples

The main way to get config strings is:
```php
$cfg = Q3DemoParser::getFriendlyConfig(<demo-file-name>);
```
You will get an array as result (or null in case of errors) and main keys are:
  * `$cfg['client']` - client vars  
  * `$cfg['client_version']` - game client version (ex: *dfengine 1.08 win-x86 Jan 20 2010*)
  * `$cfg['physic']` - *vq3* or *cpm* value
  * `$cfg['game']` - game variables, most of them you are interesting
  * `$cfg['player']` - player vars, pay attention on `hc` value in it: it's a 'handicap' setting
  * `$cfg['raw']` - raw config strings taken from demo