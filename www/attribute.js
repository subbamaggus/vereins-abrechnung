const attributeApp = Vue.createApp({
  data() {
    return {
      newAttribute: {
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
            // Add a property to each group for the new attribute item's name
            this.attributes.forEach(group => {
                group.newAttributeItemName = '';
            });
            this.newAttribute.name = '';
        } catch (e) {
            this.error = e;
        }
    },
    async saveGroup(groupid, value) {
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('groupid', groupid);
      formData.append('text', value);

      try {
        const response = await fetch('api.php?method=save_group', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to add group');
        }

        const result = await response.json();
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
    async saveAttribute(groupid, attributeid, value) {
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('groupid', groupid);
      formData.append('attributeid', attributeid);
      formData.append('text', value);

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
          <strong>Group</strong>
          <input type="text" v-model="group.name" /><a href="#" @click.prevent="saveGroup(group.id, group.name)">save</a>
          <br/>
          <label v-for="attr in group.attribute" :key="attr.id" style="margin-right: 10px; margin-left: 5px;">
            Attribute:
            <input type="text" v-model="attr.name"/><a href="#" @click.prevent="saveAttribute(group.id, attr.id, attr.name)">save</a>
            <br/>
          </label>
          <label>
            New Attribute:
            <input type="text" v-model="group.newAttributeItemName"/><a href="#" @click.prevent="saveAttribute(group.id, -1, group.newAttributeItemName)">save</a>
          </label>
          <br/>-----
        </div>
        <strong>New Group</strong>
        <input type="text" v-model="newAttribute.name"/>
        <a href="#" @click.prevent="saveGroup(-1, newAttribute.name)">save</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
attributeApp.mount('#attribute-app');
