<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vue Liste</title>
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body>

<div id="navi"></div>

<div id="app"></div>

<script>
  const navi = Vue.createApp({
    setup() {
      const data = Vue.ref([]);
      const loading = Vue.ref(true);
      const error = Vue.ref(null);

      const fetchData = async () => {
        try {
          const jsonUrl = 'api.php?method=get_years';
          const response = await fetch(jsonUrl);

          if (!response.ok) {
            throw new Error(`HTTP Fehler! Status: ${response.status}`);
          }
          const result = await response.json();
          data.value = result;
        } catch (e) {
          error.value = e;
        } finally {
          loading.value = false;
        }
      };

      Vue.onMounted(fetchData);

      return {
        data,
        loading,
        error
      };
    },
    template: `<div v-for="item in data" :key="item.id" style="margin-bottom: 15px; border: 1px solid #3656beff; padding: 10px;">
            {{ item.year }}
          </div>`

  });
  navi.mount('#navi');

  const app = Vue.createApp({
    setup() {
      const data = Vue.ref([]);
      const loading = Vue.ref(true);
      const error = Vue.ref(null);

      const fetchData = async () => {
        try {
          const jsonUrl = 'api.php';
          const response = await fetch(jsonUrl);

          if (!response.ok) {
            throw new Error(`HTTP Fehler! Status: ${response.status}`);
          }
          const result = await response.json();
          data.value = result;
        } catch (e) {
          error.value = e;
        } finally {
          loading.value = false;
        }
      };

      Vue.onMounted(fetchData);

      return {
        data,
        loading,
        error
      };
    },
    template: `
      <!-- Zeigt "Lädt..." an, solange die Daten geladen werden -->
      <div v-if="loading">Lädt...</div>

      <!-- Zeigt eine einfache Fehlermeldung an, wenn ein Fehler auftritt -->
      <div v-else-if="error">Fehler: {{ error.message }}</div>

      <!-- Zeigt die Liste an, wenn die Daten erfolgreich geladen wurden -->
      <div v-else>
        <h1>Posts aus JSON-URL</h1>
        <div>
          <div v-for="item in data" :key="item.id" style="margin-bottom: 15px; border: 1px solid #ccc; padding: 10px;">
            {{ item.name }} : {{ item.value }}
          </div>
        </div>
      </div>
    `
  });

  app.mount('#app');


</script>

</body>
</html>
