<!DOCTYPE html>
<html>
<head>
  <title>{$pageTitle}</title>
  <meta charset="utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
{if isset($description) }
  <meta name="description" content="{$description}" />
{/if}
{if isset($keywords)}
  <meta name="keywords" content="{$keywords}" />
{/if}

{foreach from=$__links item=link}
  <link href="{$link}" rel="stylesheet" type="text/css" />
{/foreach}

</head>
<body>
  {$content}

{foreach from=$__javascript item=script}
    <script src="{$script}"></script>
{/foreach}

{if $enableGA}
{literal}
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '{/literal}{$gaCode}{literal}', 'auto');
  ga('send', 'pageview');

</script>
{/literal}
{/if}
</body>
</html>

