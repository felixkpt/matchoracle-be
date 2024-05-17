import Str from '@/utils/Str';
import debounce from 'lodash/debounce';
import CheckboxTreeManager from './CheckboxTreeManager';
import { RouteCollectionInterface } from '@/interfaces/RolePermissionsInterfaces';
import { PermissionInterface } from '@/interfaces/RolePermissionsInterfaces';
import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";
import React, { useState } from 'react';
import { Icon } from '@iconify/react/dist/iconify.js';

interface Props {
    child: RouteCollectionInterface;
    permissions: PermissionInterface[];
    allPermissions: PermissionInterface[];
    indent: number
    counter: number
    isInitialRender: boolean
    hiddenIds: string[]
    setHiddenIds: React.Dispatch<React.SetStateAction<string[]>>
}

// Constants used for managing the component behavior
const PARENT_FOLDER_ID_PREFIX = 'parent-folder-';
const MAIN_CONTAINER_CLASS = 'nested-routes';
const PARENT_CHILDREN_CLASS = 'parent-children';
const ROUTE_CHECKBOX_CLASS = 'routecheckbox';

const checkboxTreeManager = new CheckboxTreeManager(
    PARENT_FOLDER_ID_PREFIX,
    ROUTE_CHECKBOX_CLASS,
    MAIN_CONTAINER_CLASS
);

// Function to get the hidden for a route based on its permissions
function getHiddenState(allPermissions: any[], uri: string) {
    let hidden = false
    if (allPermissions) {

        const res = allPermissions.find((permission) => permission.uri == uri)
        if (res)
            hidden = res.hidden === null ? false : res.hidden

    }

    return hidden

}

// Function to get the icon for a route based on its permissions
function getRouteIcon(allPermissions: any[], uri: string) {
    let icon = ''
    if (allPermissions) {

        const res = allPermissions.find((permission) => permission.uri == uri)
        if (res)
            icon = res.icon === null ? '' : res.icon

    }

    return icon

}

// The main RoutesTree component
const RoutesTree: React.FC<Props> = ({ child, permissions, allPermissions, indent, counter, isInitialRender, hiddenIds, setHiddenIds }) => {

    const { routes, children } = child

    indent += 1
    counter += 1

    const folder = child.folder
    const currentId = Str.slug((folder).replace(/^\//, ''));

    const parentFolder = (folder).replace(/^\//, '');

    const parentChecked = !!found(parentFolder, permissions)

    // Function to handle toggling checkboxes for parent routes
    function handleToggleCheck(parentId: string, action: boolean | null = null) {

        const targetElement = document.getElementById(parentId) as HTMLElement; // Narrow the type to HTMLInputElement
        const targetCheckbox = targetElement.querySelector(`.parent-checkbox`) as HTMLInputElement;

        if (targetCheckbox) {

            // real user clicking on handleToggleCheck
            if (typeof action !== 'boolean') {

                action = targetCheckbox.checked

                const inputs = targetElement.querySelectorAll<HTMLInputElement>(`input[id$="-child-checkbox"]:not([disabled]), input[id$="-parent-checkbox"]`);
                const unchecked = targetElement.querySelectorAll(`input[type="checkbox"]:not(:checked).${ROUTE_CHECKBOX_CLASS}`).length;

                inputs.forEach((input) => {
                    input.indeterminate = false
                    if (action !== null)
                        input.checked = action
                });

                const hasDisabled = !!targetElement.querySelectorAll<HTMLInputElement>(`input[id$="-child-checkbox"]:disabled`).length;
                targetCheckbox.indeterminate = unchecked !== 0 ? false : hasDisabled

                // Work on children
                const children = targetElement.querySelectorAll(`div[id^="${PARENT_FOLDER_ID_PREFIX}"]`);
                children.forEach((child: Element) => {
                    const targetCheckbox = child.querySelector(`.parent-checkbox`) as HTMLInputElement;
                    const hasDisabled = !!child.querySelectorAll<HTMLInputElement>(`input[id$="-child-checkbox"]:disabled`).length;
                    const unchecked = child.querySelectorAll(`input[type="checkbox"]:not(:checked).${ROUTE_CHECKBOX_CLASS}`).length;
                    const checked = child.querySelectorAll(`input[type="checkbox"]:checked.${ROUTE_CHECKBOX_CLASS}`).length;

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
                const checked = targetElement.querySelectorAll(`input[type="checkbox"]:checked.${ROUTE_CHECKBOX_CLASS}`).length;
                const unchecked = targetElement.querySelectorAll(`input[type="checkbox"]:not(:checked).${ROUTE_CHECKBOX_CLASS}`).length;

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
                checkboxTreeManager.checkUp(targetElement)
            }
            else {
                // should uncheckup recursivley (:- <<<---recursion
                checkboxTreeManager.uncheckUp(targetElement)
            }
        }
    }

    // Function to handle toggling the display of child routes
    function handleToggle(id: string) {
        const target = document.getElementById(id);
        target?.classList.toggle('d-none');

        const targetUpdated = document.getElementById(id);
        const isHidden = targetUpdated?.classList.contains('d-none')

        if (isHidden) {
            if (!hiddenIds.find((_id) => _id === id)) {
                setHiddenIds((curr) => [...curr, id])
            }
        } else {
            setHiddenIds(hiddenIds.filter((_id) => _id !== id))
        }
    }

    function handleCheckedSingle(e: Event | null = null) {
        if (!e) return;

        const target = e.target as HTMLInputElement;

        const action = target.checked;

        const parentElement = target.closest(`div[id^="${PARENT_FOLDER_ID_PREFIX}"]`);

        if (!parentElement) return;

        const parentCheckbox = parentElement.querySelector<HTMLInputElement>(`input[id$="-parent-checkbox"]`);

        if (!parentCheckbox) return;

        const checked = parentElement.querySelectorAll<HTMLInputElement>(`input[type="checkbox"]:checked.${ROUTE_CHECKBOX_CLASS}`).length;
        const unchecked = parentElement.querySelectorAll<HTMLInputElement>(`input[type="checkbox"]:not(:checked).${ROUTE_CHECKBOX_CLASS}`).length;

        if (unchecked === 0) {
            parentCheckbox.indeterminate = false;
            parentCheckbox.checked = true;
        } else {
            if (checked > 0) {
                parentCheckbox.indeterminate = true;
                parentCheckbox.checked = false;
            } else {
                parentCheckbox.indeterminate = false;
                parentCheckbox.checked = false;
            }
        }

        const key = parentElement.id;

        handleToggleCheck(key, action);
    }

    // Use the debounce function for checkbox checking logic
    const debouncedHandleCheckedSingle = debounce(handleCheckedSingle, 100);

    // Function to find a permission based on uriMethods
    function found(uriMethods: string, permissions: any) {
        return !!permissions?.find((permission: RouteInterface) => permission.uri === uriMethods);
    }

    // Function to determine whether a checkbox should be checked or not
    function shouldCheckChildCheckbox(route: RouteInterface, permissions: PermissionInterface[], parentChecked: boolean) {

        const inputId: string = `${Str.uriMethods(route.uri_and_methods)}-child-checkbox`

        setTimeout(() => {

            const exists = parentChecked === false ? route.checked : route.checked || found(route.uri_and_methods, permissions)

            if (exists) {
                const checkbox = document.getElementById(inputId) as HTMLInputElement

                if (checkbox) {
                    checkbox.checked = true
                    const parentElement = checkbox.closest(`div[id^="${PARENT_FOLDER_ID_PREFIX}"]`)
                    if (parentElement) {
                        const key = parentElement.id
                        handleToggleCheck(key, true)
                    } else {
                        console.log('no parent')
                    }
                }
            }

        }, 200);

    }

    // React state to track order of items
    const [itemList, setItemList] = useState(children);

    // Function to update list on drop
    const handleDrop = (droppedItem: any) => {

        // Ignore drop outside droppable container
        if (!droppedItem.destination) return;
        const updatedList = [...itemList];
        // Remove dragged item
        const [reorderedItem] = updatedList.splice(droppedItem.source.index, 1);
        // Add dropped item
        updatedList.splice(droppedItem.destination.index, 0, reorderedItem);
        // Update State
        setItemList(updatedList);
        isInitialRender = true
    };

    // React state to track order of items
    const [routesList, setRoutesList] = useState(routes);

    // Function to update list on drop
    const handleDropRoutes = (droppedItem: any) => {

        // Ignore drop outside droppable container
        if (!droppedItem.destination) return;
        const updatedList = [...routesList];
        // Remove dragged item
        const [reorderedItem] = updatedList.splice(droppedItem.source.index, 1);
        // Add dropped item
        updatedList.splice(droppedItem.destination.index, 0, reorderedItem);
        // Update State
        setRoutesList(updatedList);
        isInitialRender = true
    };

    let filname: string | null = null
    let echo: string | null = null

    return (
        <div key={currentId} className={`mt-1 parent-folder ps-${indent} border-start border-primary-subtle p-1`} id={`${PARENT_FOLDER_ID_PREFIX}${currentId}`}>
            <div className='col-12 folder-section toggler-section mb-2 ms-1 px-1 border bg-white row align-items-center justify-content-between rounded'>
                <div className='col-7 d-flex'>
                    <label className='form-check-label px-0.5 d-flex align-items-center cursor-pointer'>
                        <input
                            type='checkbox'
                            id={`${currentId}-parent-checkbox`}
                            value={parentFolder}
                            className='form-check-input me-2 parent-checkbox'
                            onChange={() => handleToggleCheck(`${PARENT_FOLDER_ID_PREFIX}${currentId}`)}
                            data-counter={counter}
                        />
                        <input
                            type='hidden'
                            className='folder-title'
                            value={folder}
                        />
                    </label>
                    <label className="toggler text-base flex-grow-1 border flex-grow-1 py-2 ps-1 pe-0 rounded" onClick={() => handleToggle(`chld-${PARENT_FOLDER_ID_PREFIX}${currentId}-${PARENT_CHILDREN_CLASS}`)}>
                        <h5>{Str.title(Str.afterLast(child.folder, '/') || '--')}</h5>
                    </label>
                </div>
                <div className='bg-light col-5 d-flex rounded gap-1'>
                    <label className='col-6'>
                        <input placeholder='Icon' defaultValue={getRouteIcon(allPermissions, parentFolder)} style={{ width: '100px' }} type="text" id={`${parentFolder}-folder-icon`} className='folder-icon ms-1 border border-secondary rounded' />
                    </label>
                    <div className='col-6'>
                        <label className='form-check-label w-50 d-flex ms-auto justify-content-end align-items-center cursor-pointer'>
                            <input
                                type='checkbox'
                                id={`${currentId}-folder-hidden`}
                                className='form-check-input me-2 folder-hidden'
                                defaultChecked={getHiddenState(allPermissions, parentFolder)}
                            />
                            Hidden
                        </label>
                        <input
                            type='hidden'
                            className='folder-is_public'
                            value={`false`}
                        />
                    </div>
                </div>
            </div>
            <div>
                <div id={`chld-${PARENT_FOLDER_ID_PREFIX}${currentId}-${PARENT_CHILDREN_CLASS}`} className={`${PARENT_CHILDREN_CLASS} my-1 ms-2 shadow-sm px-1 py-2 ${hiddenIds.includes(`chld-${PARENT_FOLDER_ID_PREFIX}${currentId}-${PARENT_CHILDREN_CLASS}`) ? 'd-none' : ''}`}>
                    {
                        routesList.length > 0 &&
                        <DragDropContext onDragEnd={handleDropRoutes}>
                            <Droppable droppableId={currentId}>
                                {(provided: any) => (

                                    <table className='routes-table table table-responsive shadow-sm table-striped table-hover'
                                        {...provided.droppableProps}
                                        ref={provided.innerRef}>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th className='align-text-start'>Route name</th>
                                                <th className='align-text-start'>Methods</th>
                                                <th className='align-text-start'>Icon/Hidden</th>
                                            </tr>
                                        </thead>
                                        <>

                                            {routesList.map((route, i) => {

                                                return (
                                                    <Draggable key={currentId + i} draggableId={currentId + i} index={i}>
                                                        {(provided: any) => {

                                                            if (echo && route.filename == echo) {
                                                                echo = null
                                                            }

                                                            if (filname === null || filname != route.filename) {
                                                                filname = route.filename
                                                                echo = filname
                                                            }

                                                            return (
                                                                <tbody key={`${i}+${folder}_${route.slug}`}
                                                                    ref={provided.innerRef}
                                                                    {...provided.dragHandleProps}
                                                                    {...provided.draggableProps}>
                                                                    {
                                                                        echo &&
                                                                        <tr>
                                                                            <td colSpan={4} className='col-12 fst-italic'>--- {echo} ---</td>
                                                                        </tr>
                                                                    }
                                                                    <tr className='link routes-parent route-section' style={{ opacity: route.checked ? 0.5 : 1 }}>
                                                                        <td className='col-1 border border-right cursor-pointer'>
                                                                            <label className="form-check-label py-1 px-3 d-flex gap-2 rounded w-100 cursor-pointer" title={route.uri_and_methods}>
                                                                                <input
                                                                                    type='checkbox'
                                                                                    value={route.uri_and_methods}
                                                                                    id={`${Str.uriMethods(route.uri_and_methods)}-child-checkbox`}
                                                                                    className={`${ROUTE_CHECKBOX_CLASS} form-check-input me-2`}
                                                                                    onChange={(e) => debouncedHandleCheckedSingle(e, currentId, true, true)}
                                                                                    defaultChecked=
                                                                                    {(permissions.length && shouldCheckChildCheckbox(route, permissions, parentChecked)) || false}
                                                                                    disabled={route.checked}
                                                                                />
                                                                                <input
                                                                                    type='hidden'
                                                                                    className='route-title'
                                                                                    value={route.title}
                                                                                />
                                                                                <input
                                                                                    type='hidden'
                                                                                    className='folder-hidden'
                                                                                    value={route.hidden.toString()}
                                                                                />
                                                                                <input
                                                                                    type='hidden'
                                                                                    className='folder-is_public'
                                                                                    value={route.is_public.toString()}
                                                                                />
                                                                            </label>
                                                                        </td>
                                                                        <td className='col-6 align-text-start' title={route.uri_and_methods}>
                                                                            {route.title}
                                                                        </td>
                                                                        <td className='col-3 align-text-start' title={route.methods || 'GET'}>
                                                                            <span className="d-inline-block text-truncate" style={{ maxWidth: '170px' }}>
                                                                                {route.methods || 'GET'}
                                                                            </span>
                                                                        </td>
                                                                        <td className='col-2 align-text-start'>
                                                                            <div className='bg-light col-4 d-flex justify-content-between rounded gap-1'>
                                                                                <label>
                                                                                    <input placeholder='Icon' defaultValue={getRouteIcon(allPermissions, route.uri_and_methods)} style={{ width: '100px' }} type="text" className='folder-icon ms-1 border border-secondary rounded' />
                                                                                </label>
                                                                                <label className='form-check-label d-flex align-items-center cursor-pointer'>
                                                                                    <input
                                                                                        type='checkbox'
                                                                                        defaultChecked={route.hidden}
                                                                                        className='d-none folder-hidden'
                                                                                    />
                                                                                    {route.hidden ? 'True' : 'False'}
                                                                                </label>
                                                                                <label>
                                                                                    <input
                                                                                        type='hidden'
                                                                                        value={route.is_public.toString()}
                                                                                        className='d-nonen folder-is_public'
                                                                                    />
                                                                                </label>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            )
                                                        }
                                                        }
                                                    </Draggable>
                                                )
                                            })
                                            }
                                        </>
                                    </table>
                                )}
                            </Droppable>
                        </DragDropContext>
                    }

                    <>
                        {
                            itemList.length > 0 &&
                            <div className={`has-dropdown ps-${indent}`}>
                                <DragDropContext onDragEnd={handleDrop}>
                                    <Droppable droppableId={currentId}>
                                        {(provided: any) => (
                                            <div className={`list-unstyled dropdown COUNTER${counter}`}
                                                {...provided.droppableProps}
                                                ref={provided.innerRef}>
                                                {
                                                    itemList.map((child, i: number) => {
                                                        return <Draggable key={currentId + i} draggableId={currentId + i} index={i}>
                                                            {(provided: any) => (
                                                                <div
                                                                    className="item-container ps-1 my-1 rounded text-dark d-flex gap-2 align-items-center border draggable"
                                                                    ref={provided.innerRef}
                                                                    {...provided.dragHandleProps}
                                                                    {...provided.draggableProps}
                                                                >
                                                                    <Icon icon="carbon:drag-vertical"></Icon>
                                                                    <div className='col tree-section'>
                                                                        <RoutesTree key={child.folder} indent={indent} child={child} permissions={permissions} allPermissions={allPermissions} counter={counter} isInitialRender={isInitialRender} hiddenIds={hiddenIds} setHiddenIds={setHiddenIds} />
                                                                    </div>
                                                                </div>
                                                            )}
                                                        </Draggable>

                                                    })

                                                }
                                            </div>
                                        )}
                                    </Droppable>
                                </DragDropContext>
                            </div>
                        }
                    </>
                </div>
            </div>
        </div>
    )
}

export default RoutesTree