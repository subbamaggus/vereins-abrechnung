<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<script type="module">
  import { createApp, ref } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'

  createApp({
    setup() {
      const message = ref('Hello Vue!')
      return {
        message
      }
    }
  }).mount('#app')
</script>
</head>
<body>

<h1>This is a Heading</h1>
<p>This is a paragraph.</p>

<div id="app">{{ message }}</div>

</body>
</html>