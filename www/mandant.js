const mandantApp = Vue.createApp({
  data() {
    return {
      newMandant: {
        name: '',
      },
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
            this.mandants.forEach(group => {
                group.newUserMail = '';
            });
            this.newMandant.name = '';
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
    async saveMandant(mandantid, value) {
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('mandantid', mandantid);
      formData.append('text', value);

      try {
        const response = await fetch('api.php?method=save_mandant', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to saveMandant');
        }

        const result = await response.json();
        if (result.success) {
          this.success = true;
          this.fetchMandants();
          this.fetchAllUsers();
        } else {
          throw new Error('Failed to saveMandant');
        }
      } catch (e) {
        this.error = e.message;
      }
    },
    async saveUser(mandantid, usermail, value) {
      console.log('save: ' + mandantid + ' : ' + usermail + ' : ' + value);
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('mandantid', mandantid);
      formData.append('usermail', usermail);
      formData.append('text', value);

      try {
        const response = await fetch('api.php?method=save_user', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to saveUser');
        }

        const result = await response.json();
        if (result.success) {
          this.success = true;
          this.fetchMandants();
          this.fetchAllUsers();
        } else {
          throw new Error('Failed to saveUser');
        }
      } catch (e) {
        this.error = e.message;
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
          <input type="text" v-model="mandant.name" /><a href="#" @click.prevent="saveMandant(mandant.mid, mandant.name)">save</a>
          <br/>
          <label v-for="user in mandant.user" :key="user.id" style="margin-right: 10px; margin-left: 5px;">
            User:
            <select v-model="user.email">
              <option v-for="u in allUsers" :key="u.id" :value="u.email">{{ u.email }}</option>
            </select>
            <input type="text" v-model="user.privilege" />
            <a href="#" @click.prevent="saveUser(mandant.mid, user.email, user.privilege)">save</a>
            <br/>
          </label>
          <label>
            New User:
            <input type="text" v-model="mandant.newUserMail"/><a href="#" @click.prevent="saveUser(mandant.mid, mandant.newUserMail, -1)">save</a>
          </label>
          <br/>-----
        </div>
        <strong>New Mandant</strong>
        <input type="text"  v-model="newMandant.name"/>
        <a href="#" @click.prevent="saveMandant(-1, newMandant.name)">save</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
mandantApp.mount('#mandant-app');
