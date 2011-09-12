<?php

$str = <<<EOF
<html>
    <head>
        <title>{Title}</title>
    </head>
    <body>
        ...
    </body>
</html>
EOF;

$str = str_ireplace(
        array(
            '{Title}'
            ),
        array(
            'Ini adalah judul'
            ),
        $str
    );

echo $str;
?>