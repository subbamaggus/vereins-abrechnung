const mandantApp = Vue.createApp({
  data() {
    return {
      mandants: [],
      error: null,
      success: false,
    };
  },
  methods: {
    async fetchMandants() {
        try {
            const response = await fetch('api.php?method=get_mandants');
            if (!response.ok) {
                throw new Error('Could not fetch mandants');
            }
            this.mandants = await response.json();
        } catch (e) {
            this.error = e;
        }
    },
  },
  mounted() {
    this.fetchMandants();
  },  
  template: `
    <div>
      <h1>Manage Mandants</h1>

      <div style="margin-bottom: 10px;">
        <div v-for="mandant in mandants" :key="mandant.mid" style="margin-bottom: 5px;">
          <strong>Mandant</strong>
          <input type="text" v-model="mandant.name" /><a href="#">speichern</a>
          <br/>
          <label v-for="user in mandant.user" :key="user.id" style="margin-right: 10px; margin-left: 5px;">
            <input type="text" v-model="user.email" />
            <input type="text" v-model="user.privilege" />
            <a href="#">speichern</a>
            <br/>
          </label>
          <label>
            <input type="text" /><a href="#">neu</a>
          </label>
          <br/>-----
        </div>
        <strong>Mandant</strong>
        <input type="text" />
        <a href="#">neu</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
mandantApp.mount('#mandant-app');
