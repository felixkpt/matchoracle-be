import { PermissionInterface, RouteInterface } from "@/interfaces/RolePermissionsInterfaces";
import Str from "@/utils/Str";

class CheckboxTreeManager {

    private PARENT_FOLDER_ID_PREFIX: string;
    private ROUTE_CHECKBOX_CLASS: string;
    private MAIN_CONTAINER_CLASS: string;
    private rolePermissions: PermissionInterface[] = [];

    constructor(
        parentFolderIdPrefix: string,
        routeCheckboxClass: string,
        mainContainerClass: string,
    ) {
        this.PARENT_FOLDER_ID_PREFIX = parentFolderIdPrefix;
        this.ROUTE_CHECKBOX_CLASS = routeCheckboxClass;
        this.MAIN_CONTAINER_CLASS = mainContainerClass;
    }

    setRolePermissions(rolePermissions: PermissionInterface[],
    ) {
        this.rolePermissions = rolePermissions;
    }
    // Recursive function to handle checking parent checkboxes upwards in the tree
    checkUp(parentElement: HTMLElement) {
        // Find the closest grandparent
        let grandParent = parentElement;

        // this will help us not repeat the action on targetFolder
        const targetFolderId = grandParent.id

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


            if (grandParent.parentElement) {
                grandParent = grandParent.parentElement;
            }

            if (grandParent?.classList.contains(this.MAIN_CONTAINER_CLASS)) {
                break;
            }

        }
    }

    uncheckUp(targetElement: any) {

        // Find the closest grandparent
        let grandParent = targetElement;

        // this will help us not repeat the action on targetFolder
        const targetFolderId = grandParent.id

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

    // Function to handle toggling checkboxes for parent routes
    handleToggleCheck(parentId: string, action: boolean | null = null) {

        const targetElement = document.getElementById(parentId) as HTMLElement; // Narrow the type to HTMLInputElement
        const targetCheckbox = targetElement.querySelector(`.parent-checkbox`) as HTMLInputElement;

        if (targetCheckbox) {

            // real user clicking on handleToggleCheck
            if (typeof action !== 'boolean') {

                action = targetCheckbox.checked

                const inputs = targetElement.querySelectorAll<HTMLInputElement>(`input[id$="-child-checkbox"]:not([disabled]), input[id$="-parent-checkbox"]`);
                const unchecked = targetElement.querySelectorAll(`input[type="checkbox"]:not(:checked).${this.ROUTE_CHECKBOX_CLASS}`).length;

                inputs.forEach((input) => {
                    input.indeterminate = false
                    if (action !== null)
                        input.checked = action
                });

                const hasDisabled = !!targetElement.querySelectorAll<HTMLInputElement>(`input[id$="-child-checkbox"]:disabled`).length;
                targetCheckbox.indeterminate = unchecked !== 0 ? false : hasDisabled

                // Work on children
                const children = targetElement.querySelectorAll(`div[id^="${this.PARENT_FOLDER_ID_PREFIX}"]`);
                children.forEach((child: Element) => {
                    const targetCheckbox = child.querySelector(`.parent-checkbox`) as HTMLInputElement;
                    const hasDisabled = !!child.querySelectorAll<HTMLInputElement>(`input[id$="-child-checkbox"]:disabled`).length;
                    const unchecked = child.querySelectorAll(`input[type="checkbox"]:not(:checked).${this.ROUTE_CHECKBOX_CLASS}`).length;
                    const checked = child.querySelectorAll(`input[type="checkbox"]:checked.${this.ROUTE_CHECKBOX_CLASS}`).length;

                    if (checked === 0) {
                        targetCheckbox.checked = false;
                        targetCheckbox.indeterminate = hasDisabled;
                    } else {
                        if (unchecked === 0) {
                            targetCheckbox.checked = true;
                            targetCheckbox.indeterminate = false;
                            return
                        } else {
                            targetCheckbox.indeterminate = true;
                        }
                    }

                })

            } else {
                // assist programmatic click
                const checked = targetElement.querySelectorAll(`input[type="checkbox"]:checked.${this.ROUTE_CHECKBOX_CLASS}`).length;
                const unchecked = targetElement.querySelectorAll(`input[type="checkbox"]:not(:checked).${this.ROUTE_CHECKBOX_CLASS}`).length;

                if (checked === 0) {
                    targetCheckbox.checked = false;
                    targetCheckbox.indeterminate = false;
                } else {
                    if (unchecked === 0) {
                        targetCheckbox.checked = true;
                        targetCheckbox.indeterminate = false;
                    } else {
                        targetCheckbox.indeterminate = true;
                    }
                }

            }

            if (action === true) {
                // should checkup recursivley -:) <<<---recursion
                this.checkUp(targetElement)
            }
            else {
                // should uncheckup recursivley (:- <<<---recursion
                this.uncheckUp(targetElement)
            }
        }
    }

    // Function to find a permission based on uriMethods
    found(uriMethods: string, rolePermissions: any) {
        return !!rolePermissions?.find((permission: RouteInterface) => permission.uri === uriMethods);
    }

    // Function to determine whether a checkbox should be checked or not
    shouldCheckChildCheckbox(route: RouteInterface, parentChecked: boolean) {

        const inputId: string = `${Str.uriMethods(route.uri_and_methods)}-child-checkbox`

        const exists = parentChecked === false ? route.checked : route.checked || this.found(route.uri_and_methods, this.rolePermissions)

        if (exists) {
            const checkbox = document.getElementById(inputId) as HTMLInputElement

            if (checkbox) {
                checkbox.checked = true
                const parentElement = checkbox.closest(`div[id^="${this.PARENT_FOLDER_ID_PREFIX}"]`)
                if (parentElement) {
                    const key = parentElement.id
                    this.handleToggleCheck(key, true)
                } else {
                    console.log('no parent')
                }
            }
        }

    }

}

export default CheckboxTreeManager