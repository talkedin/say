<?php  

$str = <<<EOF
<html>
    <head>
        <title>{Title}</title>
    </head>
    <body>
        [code]
            ini isi code
        [/code]
    </body>
</html>
EOF;

$str = preg_replace(
            array(
                '/\{Title\}/is',
                '/\[br\]/is',
                '/\[code\](.*?)\[\/code\]/is',
            ),
            array(
                'Ini Judul',
                '<br />',
                '<pre class="code">$1</pre>',
            ),
            $str
        );

echo $str;
?>  