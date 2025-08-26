
const app = Vue.createApp({
  setup() {
    const data = Vue.ref([]);
    const loading = Vue.ref(true);
    const error = Vue.ref(null);
    const fetchData = async () => {
      try {
        const jsonUrl = 'api.php?method=get_items';
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
      <h1>Daten</h1>
      <table class="table table-striped">
        <tr v-for="item in data" :key="item.id">
          <th scope="row">{{ item.date }}</th>  
          <td>{{ item.name }}</td> 
          <td>{{ item.value }}</td>  
        </tr>
      </table>
    </div>
  `
});
app.mount('#app');
