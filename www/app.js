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
    <div v-if="loading">Lädt...</div>
    <div v-else-if="error">Fehler: {{ error.message }}</div>
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
