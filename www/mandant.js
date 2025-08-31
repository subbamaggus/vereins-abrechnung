
const mandant = Vue.createApp({
  setup() {
    const data = Vue.ref([]);
    const loading = Vue.ref(true);
    const error = Vue.ref(null);
    const fetchData = async () => {
      try {
        const jsonUrl = 'api.php?method=get_mandants';
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
    <div v-for="item in data" :key="item.id">
      <form action="?method=open_mandant" method="get">
        <input type="hidden" name="method" value="open_mandant"/>
        <input type="hidden" name="mandant" :value=item.mid />
        {{ item.name }}<br/>
        <button type="submit" value="Submit">Submit</button>
      </form>
    </div>
  `
});
mandant.mount('#mandant');
