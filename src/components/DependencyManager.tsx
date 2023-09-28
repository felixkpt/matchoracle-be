class DependencyManager {
  constructor() {
    this.dependencyMap = new Map();
  }

  addDependency(dependentKey, dependencyKey, fetchFunction) {
    if (!this.dependencyMap.has(dependentKey)) {
      this.dependencyMap.set(dependentKey, {});
    }
    this.dependencyMap.get(dependentKey)[dependencyKey] = {
      fetchFunction,
      data: [],
    };
  }

  getDependencyOptions(dependentKey, dependencyKey) {
    const dependencyObj = this.dependencyMap.get(dependentKey)?.[dependencyKey];
    return dependencyObj ? dependencyObj.data : [];
  }

  async fetchDependency(dependentKey, dependencyKey) {
    const dependencyObj = this.dependencyMap.get(dependentKey)?.[dependencyKey];
    if (dependencyObj) {
      dependencyObj.data = await dependencyObj.fetchFunction();
    }
  }
}

const dependencyManager = new DependencyManager();
