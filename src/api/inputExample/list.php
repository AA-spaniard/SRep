<a href="/api/v1/redoc">OpenAPI(aka Swagger) spec view (Redoc)</a>

<br><br>

List of testing input examples:
<ul>
    <?php foreach (EXAMPLES_LIST as $name) { ?>
        <li>
            <a href="/api/v1/input-example/get?name=<?php echo $name; ?>"><?php echo $name; ?></a>
        </li>
    <?php } ?>
</ul>
<br>

Example testing PDF generation invocation:<br>
<pre>
    curl --data @<b>path-to-input.json</b> -X POST https://<b>branch_name</b>.pdf.abg.ltd/ ><b>path-to-output.pdf</b>
</pre>
