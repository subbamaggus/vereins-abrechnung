
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
    const handleClick = (item) => {
      let tmp = `Sie haben auf Post "${item}" geklickt.`;
      console.log(tmp);
    };
    Vue.onMounted(fetchData);
    return {
      data,
      loading,
      error,
      handleClick
    };
  },
  template: `
      <a v-for="item in data" :key="item.id" @click="handleClick(item.year)" href="#">
        {{ item.year }}&nbsp;
      </a>

  `
});
navi.mount('#navi');
