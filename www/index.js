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
      data: [],
      years: [],
      currentYear: 0,
      summary: [],
      attributes: [],
      depots: [],
      mandanten: [],
      selectedItems: [],
      selectedAttribute: null,
      bulkAction: 'add',
      selectedFilters: [],
      selectedDepots: [],
      loading: true,
      error: null,
    };
  },
  methods: {
    async applyFilters() {
        this.fetchSummary();
        this.fetchData(this.selectedFilters, this.selectedDepots);
    },
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
            window.location.reload();
        }
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
                this.fetchAttributes();
                window.location.reload();
            }
        } catch (e) {
            this.error = e;
        }
    },
    async fetchAttributes() {
        try {
            const response = await fetch('api.php?method=get_attributes');
            if (!response.ok) {
                throw new Error('Could not fetch attributes');
            }
            this.attributes = await response.json();
        } catch (e) {
            this.error = e;
        }
    },
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
    async setAttributes(itemId, attributeId) {
        try {
            const response = await fetch('api.php?method=set_attribute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&attribute_id=${attributeId}`,
            });
            if (!response.ok) {
                throw new Error('Could not set attribute');
            }
            const result = await response.json();
            if (result.success) {
                // Instead of fetching all data, update the local state
                const item = this.data.find(i => i.id === itemId);
                if (item) {
                    if (!item.attribute) {
                        item.attribute = [];
                    }
                    // Find the attribute details to add
                    let attribute_item_to_add = null;
                    for (const group of this.attributes) {
                        const found = group.attribute.find(attr => attr.id === attributeId);
                        if (found) {
                            attribute_item_to_add = found;
                            break;
                        }
                    }
                    if (attribute_item_to_add && result.inserted) {
                        item.attribute.push({ aai_id: attributeId, aai_name: attribute_item_to_add.name });
                    }
                }
            }
        } catch (e) {
            this.error = e;
        }
    },
    async resetAttributes(itemId, attributeId) {
        try {
            const response = await fetch('api.php?method=reset_attribute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&attribute_id=${attributeId}`,
            });
            if (!response.ok) {
                throw new Error('Could not reset attribute');
            }
            const result = await response.json();
            if (result.success) {
                // Instead of fetching all data, update the local state
                const item = this.data.find(i => i.id === itemId);
                if (item && item.attribute) {
                    const index = item.attribute.findIndex(attr => attr.aai_id === attributeId);
                    if (index > -1) {
                        item.attribute.splice(index, 1);
                    }
                }
            }
        } catch (e) {
            this.error = e;
        }
    },
    async setDepot(itemId, depotId) {
        try {
            const response = await fetch('api.php?method=set_depot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&depot_id=${depotId}`,
            });
            if (!response.ok) {
                throw new Error('Could not set depot');
            }
            const result = await response.json();
            if (result.success) {
                // Instead of fetching all data, update the local state
                const item = this.data.find(i => i.id === itemId);
                if (item) {
                    item.depot_id = depotId;
                }
            }
        } catch (e) {
            this.error = e;
        }
    },
    async resetDepot(itemId, depotId) {
        try {
            const response = await fetch('api.php?method=reset_depot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&depot_id=${depotId}`,
            });
            if (!response.ok) {
                throw new Error('Could not reset depot');
            }
            const result = await response.json();
            if (result.success) {
                // Instead of fetching all data, update the local state
                const item = this.data.find(i => i.id === itemId);
                if (item) {
                    item.depot_id = 0;
                }
            }
        } catch (e) {
    this.error = e;
  }
},
async deleteItem(itemId) {
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }
    try {
        const response = await fetch('api.php?method=delete_item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${itemId}`,
        });

        if (!response.ok) {
            throw new Error('Could not delete item');
        }

        const result = await response.json();
        if (result.success) {
            const index = this.data.findIndex(i => i.id === itemId);
            if (index > -1) {
                this.data.splice(index, 1);
            }
        } else {
            throw new Error(result.error || 'Failed to delete item from server');
        }
    } catch (e) {
        this.error = e;
    }
},
async updateItem(item, field, event) {
    const newValue = event.target.innerText;
    const oldValue = item[field];
    
    // Optimistically update the UI
    item[field] = newValue;

    try {
        const response = await fetch('api.php?method=update_item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: item.id,
                field: field,
                value: newValue
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Update failed');
        }

        const result = await response.json();
        if (!result.success) {
            // Revert if the server-side update failed
            item[field] = oldValue;
            this.error = new Error('Server update failed');
        }
    } catch (e) {
        // Revert if the fetch request itself fails
        item[field] = oldValue;
        this.error = e;
    }
},
async applyBulkAction() {
    if (!this.selectedItems.length || !this.selectedAttribute) {
        return;
    }

        const isAddAction = this.bulkAction === 'add';
        const endpoint = isAddAction ? 'set_attributes_bulk' : 'reset_attributes_bulk';

        try {
            const response = await fetch(`api.php?method=${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_ids: this.selectedItems,
                    attribute_id: this.selectedAttribute
                }),
            });

            if (!response.ok) {
                throw new Error(`Could not ${this.bulkAction} attributes in bulk`);
            }

            const result = await response.json();
            if (result.success) {
                // Update local data
                this.selectedItems.forEach(itemId => {
                    const item = this.data.find(i => i.id === itemId);
                    if (item) {
                        if (isAddAction) {
                            if (!item.attribute) {
                                item.attribute = [];
                            }
                            if (!item.attribute.some(attr => attr.aai_id === this.selectedAttribute)) {
                                let attribute_item_to_add = null;
                                for (const group of this.attributes) {
                                    const found = group.attribute.find(attr => attr.id === this.selectedAttribute);
                                    if (found) {
                                        attribute_item_to_add = found;
                                        break;
                                    }
                                }
                                if (attribute_item_to_add) {
                                    item.attribute.push({ aai_id: this.selectedAttribute, aai_name: attribute_item_to_add.name });
                                }
                            }
                        } else { // Remove action
                            if (item.attribute) {
                                const index = item.attribute.findIndex(attr => attr.aai_id === this.selectedAttribute);
                                if (index > -1) {
                                    item.attribute.splice(index, 1);
                                }
                            }
                        }
                    }
                });
                // Reset selection
                this.selectedItems = [];
                this.selectedAttribute = null;
            }
        } catch (e) {
            this.error = e;
        }
    },
    async clickYear(year) {
      this.currentYear = year;
    },
    async fetchData(filters = [], depots = []) {
      this.loading = true;
      this.error = null;
      try {
        // We need to check if a mandant is set. The easiest way is to try fetching data
        // that requires a mandant. If it fails in a specific way, we show the selection.
        let jsonUrl = 'api.php?method=get_items';
        if (filters.length > 0) {
            jsonUrl += '&attributes=' + filters.join(',');
        }
        if (depots.length > 0) {
            jsonUrl += '&depots=' + depots.join(',');
        }
        if(this.currentYear > 0) {
            jsonUrl += '&year=' + this.currentYear;
        }
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
    async fetchYears() {
      try {
        const jsonUrl = 'api.php?method=get_years';
        const response = await fetch(jsonUrl);
        if (!response.ok) {
          throw new Error(`HTTP Fehler! Status: ${response.status}`);
        }
        const result = await response.json();
        this.years = result;
      } catch (e) {
        error.value = e;
      } finally {
        //loading.value = false;
      }
    },
    async fetchSummary() {
      try {
        jsonUrl = 'api.php?method=get_summary';
        if(this.currentYear > 0) {
            jsonUrl += '&year=' + this.currentYear;
        }
        const response = await fetch(jsonUrl);
        if (!response.ok) {
          throw new Error(`HTTP Fehler! Status: ${response.status}`);
        }
        const result = await response.json();
        this.summary = result;
      } catch (e) {
        error.value = e;
      } finally {
        //loading.value = false;
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
  computed: {
    totalValue() {
      return this.data.reduce((sum, item) => {
        const value = parseFloat(item.value);
        return sum + (isNaN(value) ? 0 : value);
      }, 0).toFixed(2);
    },
    totalSummary() {
      myDiff = 0;
      try {
          for (const element of this.summary) {
            myDiff += element.end - element.start;
          }
      } finally {
          
      }
     return myDiff.toFixed(2);
    }
  },
  mounted() {
    this.fetchData();
    this.fetchAttributes();
    this.fetchDepots();
    this.fetchMandanten();
    this.fetchYears();
    this.fetchSummary();
  },
  template: `
    <div v-if="!loggedIn">
      <div v-if="isLogin">
          <h1>LOGIN</h1>
          <form @submit.prevent="login">
              email: <input type="text" v-model="email" /><br/>
              password: <input type="current-password" v-model="password" /><br/>
              <button type="submit">Submit</button>
          </form>
          <p v-if="loginError" style="color: red;">{{ loginError }}</p>
          <a href="#" @click.prevent="toggleForm">Don't have an account? Register here.</a>
      </div>
      <div v-else>
          <h1>REGISTER</h1>
          <form @submit.prevent="register">
              email: <input type="text" v-model="email" /><br/>
              password: <input type="new-password" v-model="password" /><br/>
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
            <li v-for="mandant in mandanten" :key="mandant.mid">
                <a href="#" @click.prevent="setMandant(mandant.mid)">{{ mandant.name }}</a>
            </li>
        </ul>
    </div>
    <div v-else>
      <div v-if="loading">Loading...</div>
      <div v-else-if="error">Error: {{ error.message }}</div>
      <div v-else>
        <h1>Overview</h1>
        <p align="right"><button @click="logout">Logout</button></p>

        <div v-if="attributes.length" style="margin-bottom: 10px;">
          <details>
            <summary>Filter</summary>
            <div v-for="item in years" :key="item.id">
                <input type="radio" :id="'year-' + item.year" name="yearPicked" :value="item.year" v-model="currentYear" @change="clickYear(item.year)" >
                <label :for="'year-' + item.year">{{ item.year }}</label>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Filter by:</strong>
                <div v-for="group in attributes" :key="group.id" style="margin-bottom: 5px;">
                    <strong>{{ group.name }}:</strong>
                    <label v-for="attr in group.attribute" :key="attr.id" style="margin-right: 10px; margin-left: 5px;">
                        <input type="checkbox" :value="attr.id" v-model="selectedFilters"> {{ attr.name }}
                    </label>
                </div>
                <div style="margin-bottom: 5px;">
                    <strong>Depots:</strong>
                    <input type="checkbox" value="0" v-model="selectedDepots"> kein Depot
                    <label v-for="depot in depots" :key="depot.id" style="margin-right: 10px; margin-left: 5px;">
                        <input type="checkbox" :value="depot.id" v-model="selectedDepots"> {{ depot.name }}
                    </label>
                </div>
                <button @click="applyFilters">Apply Filters</button>
            </div>
          </details>
        </div>

        <table class="table table-striped">
          <thead>
            <tr>
              <th></th>
              <th>Date</th>
              <th>Description</th>
              <th>Value</th>
              <th>Depot</th>
              <th v-for="attribute in attributes" :key="attribute.id">{{ attribute.name }}</th>
              <th>Image</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data" :key="item.id">
              <td><input type="checkbox" :value="item.id" v-model="selectedItems"></td>
              <td contenteditable="true" @blur="updateItem(item, 'date', $event)">{{ item.date }}</td>
              <td contenteditable="true" @blur="updateItem(item, 'name', $event)">{{ item.name }}</td>
              <td contenteditable="true" @blur="updateItem(item, 'value', $event)" style="text-align: right;">{{ item.value }}</td>
              <td>
                <span v-for="depot in depots" :key="depot.id">
                  &nbsp;<a href="#" @click.prevent="setDepot(item.id, depot.id)">{{ depot.name }}</a>
                  <span v-if="item.depot_id == depot.id">&nbsp;<b><a href="#" @click.prevent="resetDepot(item.id, depot.id)">X</a></b></span>
                  <br/>
                </span>
              </td>
              <td v-for="attribute in attributes" :key="attribute.id">
                <span v-for="attribute_item in attribute.attribute" :key="attribute_item.id">
                  &nbsp;<a href="#" @click.prevent="setAttributes(item.id, attribute_item.id)">{{ attribute_item.name }}</a>
                  <span v-for="sub in item.attribute" :key="sub.id">
                    <span v-if="sub.aai_id == attribute_item.id">&nbsp;<b><a href="#" @click.prevent="resetAttributes(item.id, attribute_item.id)">X</a></b></span>
                  </span>
                  <br/>
                </span>
              </td>
              <td>
                <div class="zoom" v-if="item.file"><img :src="item.file" style="max-width:50px; overflow: auto;"/></div>
              </td>
              <td>
                <button @click="deleteItem(item.id)">Delete</button>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="text-align: right;"><strong>Summe:</strong></td>
              <td style="text-align: right;"><strong>{{ totalValue }} &euro;</strong></td>
              <td :colspan="attributes.length + 3"></td>
            </tr>
            <tr>
              <td colspan="3" style="text-align: right;"><strong>Bilanz:</strong></td>
              <td style="text-align: right;"><strong>{{ totalSummary }} &euro;</strong></td>
              <td :colspan="attributes.length + 3"></td>
            </tr>
          </tfoot>
        </table>
        
        <br/>
        <div>
            <label><input type="radio" value="add" v-model="bulkAction"> Add</label>
            <label><input type="radio" value="remove" v-model="bulkAction"> Remove</label>
            <select v-model="selectedAttribute">
                <option :value="null" disabled>Select an attribute</option>
                <template v-for="group in attributes">
                    <optgroup :label="group.name">
                        <option v-for="attr in group.attribute" :value="attr.id">{{ attr.name }}</option>
                    </optgroup>
                </template>
            </select>
            <button @click="applyBulkAction" :disabled="!selectedItems.length || !selectedAttribute">Apply to selected</button>
          </div>
        </div>

        <br/>
        <div style="margin-bottom: 10px;">
            <strong>Mandant:</strong>
            <span v-for="mandant in mandanten" :key="mandant.mid" style="margin-left: 10px;">
                <a href="#" @click.prevent="setMandant(mandant.mid)">{{ mandant.name }}</a>
            </span>
        </div>
    </div>
  `
});
app.mount('#app');
