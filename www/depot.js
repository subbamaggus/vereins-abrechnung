const depotApp = Vue.createApp({
  data() {
    return {
      newDepot: {
        name: '',
      },
      newDepotValue: {
        date: '',
        value: '',
      },
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
            this.depots.forEach(group => {
                group.newDepotValuevalue = '';
                group.newDepotValuedate = '';
            });
            this.newDepot.name = '';
        } catch (e) {
            this.error = e;
        }
    },
    async saveDepot(depotid, depotname) {
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('depotid', depotid);
      formData.append('text', depotname);

      try {
        const response = await fetch('api.php?method=save_depot', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to add entry');
        }

        const result = await response.json();
        if (result.success) {
          this.success = true;
          //window.location.href = 'index.php';
          this.fetchDepots();
        } else {
          throw new Error('Failed to add entry');
        }
      } catch (e) {
        this.error = e.message;
      }
    },
    async saveDepotValue(depotid, entrydate, entryvalue) {
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('depotid', depotid);
      formData.append('entrydate', entrydate);
      formData.append('entryvalue', entryvalue);

      try {
        const response = await fetch('api.php?method=save_depot_value', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to add entry');
        }

        const result = await response.json();
        if (result.success) {
          this.success = true;
          //window.location.href = 'index.php';
          this.fetchDepots();
        } else {
          throw new Error('Failed to add entry');
        }
      } catch (e) {
        this.error = e.message;
      }
    },
  },
  mounted() {
    this.fetchDepots();
  },  
  template: `
    <div>
      <h1>Manage Depots</h1>

      <div style="margin-bottom: 10px;">
        <div v-for="group in depots" :key="group.id" style="margin-bottom: 5px;">
          <strong>Depot</strong>
          <input type="text" v-model="group.name" /><a href="#" @click.prevent="saveDepot(group.id, group.name)">speichern</a>
          <br/>
          <label v-for="value in group.depot_value" :key="value.id" style="margin-right: 10px; margin-left: 5px;">
            <input type="text" v-model="value.value" />
            <input type="date" v-model="value.date" />
            <a href="#" @click.prevent="saveDepotValue(group.id, value.date, value.value)">speichern</a>
            <br/>
          </label>
          <label>
            <input type="text" v-model="group.newDepotValuevalue" />
            <input type="date" v-model="group.newDepotValuedate" />
            <a href="#" @click.prevent="saveDepotValue(group.id, group.newDepotValuedate, group.newDepotValuevalue)">neu</a>
          </label>
          <br/>-----
        </div>
        <strong>Depot</strong>
        <input type="text" v-model="newDepot.name"/>
        <a href="#" @click.prevent="saveDepot(-1, newDepot.name)">neu</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
depotApp.mount('#depot-app');
