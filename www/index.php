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
        <button @click="fetchData">Fetch Data</button>
  <ul>
    <li v-for="item in shoppingItems">
      {{ item.name }} - {{ item.value }}
    </li>
  </ul>
</div>

<script>
new Vue({
  el: "#app",
  data() {
    myData = {
      shoppingItems: [
        { name: 'apple', value: '7' },
        { name: 'orange', value: '12' }
      ]
    }

    console.log("myData: " + JSON.stringify(myData))
    return myData
  },
  methods: {
    async fetchData() {
      const response = await fetch("http://localhost/v-a/www/api.php");

      var obj = {shoppingItems: "value1"};

      obj.shoppingItems = await response.json();
      this.data = obj;
      console.log("fetch: " + JSON.stringify(obj));
    }
  }
});
</script>



  </body>
</html>