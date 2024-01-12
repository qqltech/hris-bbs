<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta charset="UTF-8">
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/docsify@4/themes/vue.css" />
</head>
<body>
  <div id="app">Loading ...</div>
  <script>
    window.$docsify = {
      loadSidebar: true,
      coverpage: true,
      subMaxLevel: 3,
      repo: 'rahasia/rahasia',
      name: 'Larahan',
      search: {
        noData: {
          '/': 'No results!'
        },
        paths: 'auto',
        placeholder: {
          '/': 'Search'
        },
      loadNavbar: true,
      mergeNavbar: true,
      copyCode: {
        buttonText : 'Copy',
        errorText  : 'Error',
        successText: 'Silahkan dipaste! Dasar Tukang Copas!'
      }
      },
    }
  </script>
  <script src="//cdn.jsdelivr.net/npm/docsify@4"></script>
  <script src="//cdn.jsdelivr.net/npm/docsify/lib/plugins/zoom-image.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/docsify-copy-code"></script>
  <script src="//cdn.jsdelivr.net/npm/docsify/lib/plugins/emoji.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/docsify/lib/plugins/search.min.js"></script>
  <script src="//unpkg.com/mermaid/dist/mermaid.js"></script>
  <script src="//unpkg.com/docsify-mermaid@latest/dist/docsify-mermaid.js">
  <script src="https://unpkg.com/docsify-copy-code@2"></script>
  <script src="//cdn.jsdelivr.net/npm/prismjs@1/components/prism-php.min.js"></script>
  <script>mermaid.initialize({ startOnLoad: true });</script>
</body>
</html>