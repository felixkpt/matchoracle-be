class ListSources {
    constructor() {
      this.rolesListArray = [];
      this.directPermissionsListArray = [];
      this.issueSourceListArray = [];
      this.dispositionListArray = [];
    }
  
    async rolesList() {
      const res = await getRoles('admin/settings/role-permissions/roles?all=1');
      this.rolesListArray.push(res);
      return this;
    }
  
    async directPermissionsList(role_id) {
      const res = await getRoles('admin/settings/role-permissions/permissions?all=1');
      this.directPermissionsListArray.push(res);
      return this;
    }
  
    async issueSourceList() {
      const res = await getRoles('admin/settings/picklists/tickets/issuesources?all=1');
      this.issueSourceListArray.push(res);
      return this;
    }
  
    async dispositionList(issue_source) {
      const res = await getRoles('admin/settings/role-permissions/permissions?all=1');
      this.dispositionListArray.push(res);
      return this;
    }
  
    get(key) {
      return this[`${key}Array`];
    }
  
    getDependencies(key) {
      switch (key) {
        case 'rolesList':
          return ['directPermissionsList'];
        case 'directPermissionsList':
          return ['issueSourceList'];
        case 'issueSourceList':
          return ['dispositionList'];
        default:
          return [];
      }
    }
    
  }
  

  export default ListSources