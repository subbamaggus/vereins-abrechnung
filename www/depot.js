const depotApp = Vue.createApp({
  data() {
    return {
      depots: [],
      error: null,
      success: false,
    };
  },
  methods: {
    async fetchDepots() {
        try {
            const response = await fetch('api.php?method=get_depots');
            if (!response.ok) {
                throw new Error('Could not fetch depots');
            }
            this.depots = await response.json();
        } catch (e) {
            this.error = e;
        }
    },
  },
  mounted() {
    this.fetchDepots();
  },  
  template: `
    <div>
      <a href="index.php">Home</a>&nbsp;
      <a href="add_entry.php">Add Entry</a>&nbsp;
      <a href="attribute.php">Attribute</a>&nbsp;
      <a href="depot.php">Depot</a>&nbsp;
      <a href="mandant.php">Mandant</a>&nbsp;

      <h1>Manage Depots</h1>
      

      <div style="margin-bottom: 10px;">
        <div v-for="group in depots" :key="group.id" style="margin-bottom: 5px;">
          <strong>Depot</strong>
          <input type="text" v-model="group.name" /><a href="#">speichern</a>
          <br/>
          <label v-for="value in group.depot_value" :key="value.id" style="margin-right: 10px; margin-left: 5px;">
            <input type="text" v-model="value.value" />
            <input type="text" v-model="value.date" /><a href="#">speichern</a>
            <br/>
          </label>
          <label>
            <input type="text" /><a href="#">neu</a>
          </label>
          <br/>-----
        </div>
        <strong>Depot</strong>
        <input type="text" />
        <a href="#">neu</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
depotApp.mount('#depot-app');
