class CheckboxTreeManager {

    private PARENT_FOLDER_ID_PREFIX: string;
    private ROUTE_CHECKBOX_CLASS: string;
    private MAIN_CONTAINER_CLASS: string;

    constructor(
        parentFolderIdPrefix: string,
        routeCheckboxClass: string,
        mainContainerClass: string
    ) {
        this.PARENT_FOLDER_ID_PREFIX = parentFolderIdPrefix;
        this.ROUTE_CHECKBOX_CLASS = routeCheckboxClass;
        this.MAIN_CONTAINER_CLASS = mainContainerClass;
    }

    // Recursive function to handle checking parent checkboxes upwards in the tree
    checkUp(parentElement: HTMLElement) {
        // Find the closest grandparent
        let grandParent = parentElement;

        // this will help us not repeat the action on targetFolder
        let targetFolderId = grandParent.id

        while (grandParent) {

            if (grandParent.id.match(new RegExp("^" + this.PARENT_FOLDER_ID_PREFIX)) && grandParent.classList.contains('parent-folder') && grandParent.id != targetFolderId) {

                grandParent.querySelector('.toggler')?.classList.toggle('border-dark-subtle');

                const grandParentCheckbox = grandParent.querySelector(`input[id$="-parent-checkbox"]`) as HTMLInputElement;

                if (grandParentCheckbox) {

                    const unchecked = grandParent.querySelectorAll(`input[type="checkbox"]:not(:checked).${this.ROUTE_CHECKBOX_CLASS}`).length;

                    if (unchecked === 0) {
                        grandParentCheckbox.checked = true;
                        grandParentCheckbox.indeterminate = false;

                    } else {
                        grandParentCheckbox.checked = false;
                        grandParentCheckbox.indeterminate = true;
                    }

                }

            }


            grandParent = grandParent.parentElement;

            if (grandParent?.classList.contains(this.MAIN_CONTAINER_CLASS)) {
                break;
            }

        }
    }

    uncheckUp(targetElement: any) {

        // Find the closest grandparent
        let grandParent = targetElement;

        // this will help us not repeat the action on targetFolder
        let targetFolderId = grandParent.id

        while (grandParent) {

            if (grandParent.id.match(new RegExp("^" + this.PARENT_FOLDER_ID_PREFIX)) && grandParent.classList.contains('parent-folder') && grandParent.id != targetFolderId) {

                grandParent.querySelector('.toggler').classList.toggle('shadow-sm');

                const grandParentCheckbox = grandParent.querySelector(`input[id$="-parent-checkbox"]`);

                if (grandParentCheckbox) {

                    const checked = grandParent.querySelectorAll(`input[type="checkbox"]:checked.${this.ROUTE_CHECKBOX_CLASS}`).length;
                    const unchecked = grandParent.querySelectorAll(`input[type="checkbox"]:not(:checked).${this.ROUTE_CHECKBOX_CLASS}`).length;

                    if (checked === 0) {
                        grandParentCheckbox.checked = false;
                        grandParentCheckbox.indeterminate = false;

                    } else {
                        if (unchecked === 0) {
                            grandParentCheckbox.checked = true;
                            grandParentCheckbox.indeterminate = false;
                        } else
                            grandParentCheckbox.indeterminate = true;
                    }

                }

            }

            grandParent = grandParent.parentElement;

            if (grandParent?.classList.contains(this.MAIN_CONTAINER_CLASS)) {
                break;
            }

        }
    }

}

export default CheckboxTreeManager