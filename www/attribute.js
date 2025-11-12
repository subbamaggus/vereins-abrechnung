const attributeApp = Vue.createApp({
  data() {
    return {
      newValue: {
        name: '',
      },
      attributes: [],
      selectedAttribute: null,
      error: null,
      success: false,
    };
  },
  methods: {
    async fetchAttributes() {
        try {
            const response = await fetch('api.php?method=get_attributes');
            if (!response.ok) {
                throw new Error('Could not fetch attributes');
            }
            this.attributes = await response.json();
            this.newValue.name = '';
        } catch (e) {
            this.error = e;
        }
    },
    async saveAttribute(groupid, attributeid) {
      console.log('saveAttribute' + groupid + attributeid);
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('groupid', groupid);
      formData.append('attributeid', attributeid);
      formData.append('text', this.newValue.name);

      try {
        const response = await fetch('api.php?method=save_attribute', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to add entry');
        }

        const result = await response.json();
        console.log(result);
        if (result.success) {
          this.success = true;
          //window.location.href = 'index.php';
          this.fetchAttributes();
        } else {
          throw new Error('Failed to add entry');
        }
      } catch (e) {
        this.error = e.message;
      }
    },
  },
  mounted() {
    this.fetchAttributes();
  },  
  template: `
    <div>
      <h1>Manage Attributes</h1>

      <div style="margin-bottom: 10px;">
        <div v-for="group in attributes" :key="group.id" style="margin-bottom: 5px;">
          <strong>Gruppe</strong>
          <input type="text" v-model="group.name" /><a href="#" @click.prevent="saveAttribute(group.id, '')">speichern</a>
          <br/>
          <label v-for="attr in group.attribute" :key="attr.id" style="margin-right: 10px; margin-left: 5px;">
            <input type="text" v-model="attr.name"/><a href="#" @click.prevent="saveAttribute(group.id, attr.id)">speichern</a>
            <br/>
          </label>
          <label>
            <input type="text" v-model="newValue.name"/><a href="#" @click.prevent="saveAttribute(group.id, -1)">neu</a>
          </label>
          <br/>-----
        </div>
        <strong>Gruppe</strong>
        <input type="text" v-model="newValue.name"/>
        <a href="#" @click.prevent="saveAttribute(-1, -1)">neu</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
attributeApp.mount('#attribute-app');
