const app = Vue.createApp({
  data() {
    return {
      loggedIn: false,
      mandantSet: false,
      loginError: null,
      registerError: null,
      registerSuccess: false,
      email: '',
      password: '',
      isLogin: true,
      showAddEntry: false,
      newEntry: {
        name: '',
        value: '',
        date: new Date().toISOString().slice(0, 10),
        myimage: null,
      },
      data: [],
      mandanten: [],
      loading: true,
      error: null,
    };
  },
  methods: {
    async login() {
      this.loginError = null;
      try {
        const response = await fetch('api.php?method=login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(this.email)}&password=${encodeURIComponent(this.password)}`,
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Login failed');
        }

        const result = await response.json();
        if (result.success) {
          this.loggedIn = true;
          this.fetchData(); // This will now check for mandant
        } else {
          throw new Error('Login failed');
        }
      } catch (e) {
        this.loginError = e.message;
      }
    },
    async register() {
        this.registerError = null;
        this.registerSuccess = false;
        try {
            const response = await fetch('api.php?method=register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(this.email)}&password=${encodeURIComponent(this.password)}`,
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Registration failed');
            }

            const result = await response.json();
            if (result.success) {
                this.registerSuccess = true;
            } else {
                throw new Error('Registration failed');
            }
        } catch (e) {
            this.registerError = e.message;
        }
    },
    async logout() {
        try {
            await fetch('api.php?method=logout');
        } finally {
            this.loggedIn = false;
            this.mandantSet = false;
            this.email = '';
            this.password = '';
        }
    },
    async storeEntry() {
        const formData = new FormData();
        formData.append('name', this.newEntry.name);
        formData.append('value', this.newEntry.value);
        formData.append('date', this.newEntry.date);
        if (this.newEntry.myimage) {
            formData.append('myimage', this.newEntry.myimage);
        }

        try {
            const response = await fetch('api.php?method=store_entry', {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to add entry');
            }

            const result = await response.json();
            if (result.success) {
                this.showAddEntry = false;
                this.fetchData();
            } else {
                throw new Error('Failed to add entry');
            }
        } catch (e) {
            this.error = e;
        }
    },
    handleFileUpload(event) {
        this.newEntry.myimage = event.target.files[0];
    },
    async fetchMandanten() {
        try {
            const response = await fetch('api.php?method=get_mandants');
            if (!response.ok) {
                throw new Error('Could not fetch mandanten');
            }
            this.mandanten = await response.json();
        } catch (e) {
            this.error = e;
        }
    },
    async setMandant(mandantId) {
        try {
            const response = await fetch('api.php?method=set_mandant', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `mandant=${mandantId}`,
            });
            if (!response.ok) {
                throw new Error('Could not set mandant');
            }
            const result = await response.json();
            if (result.success) {
                this.mandantSet = true;
                this.fetchData();
            }
        } catch (e) {
            this.error = e;
        }
    },
    async fetchData() {
      this.loading = true;
      this.error = null;
      try {
        // We need to check if a mandant is set. The easiest way is to try fetching data
        // that requires a mandant. If it fails in a specific way, we show the selection.
        const jsonUrl = 'api.php?method=get_items_with_attributes';
        const response = await fetch(jsonUrl);
        
        if (response.status === 401) { // Not logged in
            this.loggedIn = false;
            this.mandantSet = false;
            this.loading = false;
            return;
        }

        // A custom check for mandant would be better, but for now we can infer it
        // If the response is ok, a mandant must be set.
        if (!response.ok) {
          // Let's assume any other error for a logged-in user means mandant is not set
          this.loggedIn = true;
          this.mandantSet = false;
          this.fetchMandanten();
          return;
        }

        const result = await response.json();
        this.data = result;
        this.loggedIn = true;
        this.mandantSet = true;
      } catch (e) {
        this.error = e;
      } finally {
        this.loading = false;
      }
    },
    toggleForm() {
        this.isLogin = !this.isLogin;
        this.loginError = null;
        this.registerError = null;
        this.registerSuccess = false;
        this.email = '';
        this.password = '';
    }
  },
  mounted() {
    this.fetchData();
  },
  template: `
    <div v-if="!loggedIn">
        <div v-if="isLogin">
            <h1>LOGIN</h1>
            <form @submit.prevent="login">
                email: <input type="text" v-model="email" /><br/>
                password: <input type="password" v-model="password" /><br/>
                <button type="submit">Submit</button>
            </form>
            <p v-if="loginError" style="color: red;">{{ loginError }}</p>
            <a href="#" @click.prevent="toggleForm">Don't have an account? Register here.</a>
        </div>
        <div v-else>
            <h1>REGISTER</h1>
            <form @submit.prevent="register">
                email: <input type="text" v-model="email" /><br/>
                password: <input type="password" v-model="password" /><br/>
                <button type="submit">Submit</button>
            </form>
            <p v-if="registerError" style="color: red;">{{ registerError }}</p>
            <p v-if="registerSuccess" style="color: green;">Registration successful! You can now log in.</p>
            <a href="#" @click.prevent="toggleForm">Already have an account? Login here.</a>
        </div>
    </div>
    <div v-else-if="!mandantSet">
        <h1>Select Mandant</h1>
        <div v-if="error">{{ error.message }}</div>
        <ul v-else>
            <li v-for="mandant in mandanten" :key="mandant.id">
                <a href="#" @click.prevent="setMandant(mandant.id)">{{ mandant.name }}</a>
            </li>
        </ul>
    </div>
    <div v-else>
      <div v-if="loading">Lädt...</div>
      <div v-else-if="error">Fehler: {{ error.message }}</div>
      <div v-else>
        <button @click="logout">Logout</button>
        <button @click="showAddEntry = !showAddEntry">{{ showAddEntry ? 'Cancel' : 'Add Entry' }}</button>
        <div v-if="showAddEntry">
            <h2>Add New Entry</h2>
            <form @submit.prevent="storeEntry">
                <label>Betrag<br>
                    <input type="number" step="0.01" v-model="newEntry.value">
                </label>
                <br>
                <label>Datum<br>
                    <input type="date" v-model="newEntry.date">
                </label>
                <br>
                <label>Bezeichnung<br>
                    <input type="text" v-model="newEntry.name">
                </label>
                <br>
                <label>Bild<br>
                    <input type="file" @change="handleFileUpload">
                </label>
                <br>
                <button type="submit">speichern</button>
            </form>
        </div>
        <h1>&Uuml;bersicht</h1>
        <table class="table table-striped">
          <tr v-for="item in data" :key="item.id">
            <th scope="row">{{ item.date }}</th>  
            <td>{{ item.name }}</td> 
            <td style="text-align: right;">{{ item.value }}</td>
            <td v-for="sub in item.attribute" :key="sub.id">
            {{ sub.aai_name }}
            </td>
            <td v-if="item.file"><div class="zoom"><img :src="item.file" height="10"/></div></td>
          </tr>
        </table>
      </div>
    </div>
  `
});
app.mount('#app');
