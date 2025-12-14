const mandantApp = Vue.createApp({
  data() {
    return {
      mandants: [],
      allUsers: [],
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
    async fetchAllUsers() {
        try {
            const response = await fetch('api.php?method=get_users');
            if (!response.ok) {
                throw new Error('Could not fetch users');
            }
            this.allUsers = await response.json();
        } catch (e) {
            this.error = e;
        }
    },
  },
  mounted() {
    this.fetchMandants();
    this.fetchAllUsers();
  },  
  template: `
    <div>
      <h1>Manage Mandants</h1>

      <div style="margin-bottom: 10px;">
        <div v-for="mandant in mandants" :key="mandant.mid" style="margin-bottom: 5px;">
          <strong>Mandant</strong>
          <input type="text" v-model="mandant.name" /><a href="#">save</a>
          <br/>
          <label v-for="user in mandant.user" :key="user.id" style="margin-right: 10px; margin-left: 5px;">
            User:
            <select v-model="user.email">
              <option v-for="u in allUsers" :key="u.id" :value="u.email">{{ u.email }}</option>
            </select>
            <input type="text" v-model="user.privilege" />
            <a href="#">save</a>
            <br/>
          </label>
          <label>
            New User:
            <input type="text" /><a href="#">save</a>
          </label>
          <br/>-----
        </div>
        <strong>New Mandant</strong>
        <input type="text" />
        <a href="#">save</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
mandantApp.mount('#mandant-app');
