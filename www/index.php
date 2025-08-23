<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vue Standalone Demo</title>
  </head>
  <body>
    <!--<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>


    
<div id="app">
  <ul>
    <li v-for="item in shoppingItems">
      {{ item.name }} - {{ item.price }}
    </li>
  </ul>
</div>

<script>
new Vue({
  el: "#app",
  data() {
    return {
      shoppingItems: [
        { name: 'apple', price: '7' },
        { name: 'orange', price: '12' }
      ]
    }
  }
});
</script>



  </body>
</html>