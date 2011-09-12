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
    
    {block:Title}
        <h3><a href="{Permalink}">{Title}</a></h3>
    {/block:Title}
</html>
EOF;

$str = preg_replace(
            array(
                '/\{Title\}/is',
                '/\[br\]/is',
                '/\[code\](.*?)\[\/code\]/is',
                '/\{block:Title\}(.*?){\/block:Title\}/is'
            ),
            array(
                'Ini Judul',
                '<br />',
                '<pre class="code">$1</pre>',
                '<title>$1</title>',
            ),
            $str
        );

echo $str;
?>  