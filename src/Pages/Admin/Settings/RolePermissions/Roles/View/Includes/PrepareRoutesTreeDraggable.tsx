import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";
import Str from "@/utils/Str"
import { useEffect, useState } from "react";
import RoutesTree from "./RoutesTree";
import { Icon } from "@iconify/react/dist/iconify.js";

// Define the Props interface for the RoutesTree component
interface Props {
  routes: RouteCollectionInterface[];
  permissions: PermissionInterface[];
  allPermissions: PermissionInterface[];
  handleSubmit: ({ folders, permissions }: any) => void;
  saving: boolean;
  savedFolders: string[]
}

interface FolderCheck {
  parentId: string;
  state: 'none' | 'checked' | 'indeterminate';
}

// Constants used for managing the component behavior
const PARENT_FOLDER_ID_PREFIX = 'parent-folder-';
const MAIN_CONTAINER_CLASS = 'nested-routes';
const ROUTE_CHECKBOX_CLASS = 'routecheckbox';

function constructMenus() {
  let topLevelFolders: HTMLElement[] = Array.from(
    document.querySelectorAll(`div.${MAIN_CONTAINER_CLASS}.main-tree.tab-pane > div`)
  );

  const selector = 'div.draggable [id^="v-pills-"][id$="-tab"]';
  const elements = document.querySelectorAll(selector);

  const orderArray: string[] = [];
  elements.forEach((element, i) => {
    const id = element.id
    if (id.match(/^v-pills-/) && id.match(/-tab$/)) {
      const extractedValue = id.replace('v-pills-', '').replace('-tab', '');
      orderArray.push(extractedValue);
    }
  });

  // Create a mapping of IDs to elements
  const idToElementMap = new Map();
  topLevelFolders.forEach(folder => {
    const id = folder.id.replace(PARENT_FOLDER_ID_PREFIX, ''); // Remove the prefix
    idToElementMap.set(id, folder);
  });

  // Sort the topLevelFolders based on the orderArray
  topLevelFolders = orderArray.map(id => idToElementMap.get(id));
  const allFolders = Array.from(idToElementMap.keys());

  let menu = constructMenu(topLevelFolders, true)
  return { folderPermissions: constructMenu(topLevelFolders), menu, allFolders }

}

function constructMenu(topLevelFolders: HTMLElement[], isMenu = false) {
  const nestedRoutes = [];

  let index = 0; // Initialize the index
  const counter = 0
  for (const container of topLevelFolders) {
    const input = container.querySelector(`input[id$="-parent-checkbox"]`) as HTMLInputElement;
    index++

    if (input && (input.checked || input.indeterminate)) {

      const titleElement = container.querySelector('.folder-title') as HTMLInputElement | null;
      const iconElement = container.querySelector('input.folder-icon') as HTMLInputElement | null;
      const hiddenElement = container.querySelector('input.folder-hidden') as HTMLInputElement | null;

      if (titleElement && iconElement && hiddenElement) {
        const title = titleElement.value;
        const icon = iconElement.value;
        const children = constructMenuRecursively(container, isMenu, counter);
        const hidden = hiddenElement.checked;

        let unchecked: string[] = []
        if (isMenu == false) {
          unchecked = getUncheckedCheckboxValues(container);
        }

        let newChildren = removeRoutelessNodes(children)

        // Check if there's at least one route
        const routes = getRoutes(container, isMenu);
        if (routes.length > 0 || hasRouteInChildren(newChildren)) {
          nestedRoutes.push({
            folder: input?.value ?? '',
            title,
            icon,
            hidden,
            children: newChildren,
            position: nestedRoutes.length + 1,
            unchecked,
            routes,
          });
        }

      }

    }
  }

  return nestedRoutes;
}

function hasRouteInChildren(children: RouteCollectionInterface[]) {
  for (const child of children) {
    if (child.routes.length > 0 || hasRouteInChildren(child.children)) {
      return true;
    }
  }
  return false;
}

function removeRoutelessNodes(children: RouteCollectionInterface[]): RouteCollectionInterface[] {
  let res = [];

  for (const child of children) {
    const hasRoutes = child.routes.length > 0 || hasRouteInChildren(child.children);

    if (hasRoutes) {
      const updatedChild = {
        ...child,
        children: removeRoutelessNodes(child.children),
      };

      res.push(updatedChild);
    }
  }

  return res;
}

function getUncheckedCheckboxValues(container: HTMLElement): string[] {
  const uncheckedFoldersUnchecked: HTMLInputElement[] = Array.from(container.querySelectorAll(`input[id$="-parent-checkbox"]:not(:checked):not(:indeterminate)`));
  const uncheckedRoutes: HTMLInputElement[] = Array.from(container.querySelectorAll(`input[type="checkbox"]:not(:checked).${ROUTE_CHECKBOX_CLASS}`));

  const unchecked: string[] = uncheckedFoldersUnchecked.concat(uncheckedRoutes).map(checkbox => checkbox.value);
  return unchecked;
}

function constructMenuRecursively(folderElement: Element | null, isMenu: boolean, counter: number): any[] {
  counter += 1;

  const childrenContainers = folderElement?.querySelectorAll(`.COUNTER${counter}>div[id^="${PARENT_FOLDER_ID_PREFIX}"]`);

  const nestedRoutes = [];

  if (childrenContainers) {
    for (const container of childrenContainers) {
      const input = container.querySelector(`input[id$="-parent-checkbox"]`) as HTMLInputElement | null;

      if (input && (input.checked || input.indeterminate)) {
        const children = constructMenuRecursively(container, isMenu, counter);

        const titleElement = container.querySelector('.folder-title') as HTMLInputElement | null;
        const iconElement = container.querySelector('input.folder-icon') as HTMLInputElement | null;
        const hiddenElement = container.querySelector('input.folder-hidden') as HTMLInputElement | null;

        if (titleElement && iconElement && hiddenElement) {
          const title = titleElement.value;
          const icon = iconElement.value;
          const hidden = hiddenElement.checked;

          nestedRoutes.push({
            folder: input.value,
            title,
            icon,
            hidden,
            children,
            routes: getRoutes(container, isMenu),
          });
        }
      }
    }
  }

  return nestedRoutes;
}

// Function to get the selected routes for a given parent container
function getRoutes(container: any, isMenu = false) {
  const id = container.id
  const routes = container.querySelectorAll(`#chld-${id}-parent-children>.routes-table .route-section`)

  const items = []
  for (const route of routes) {

    const input = route.querySelector('input[type="checkbox"]')

    if (input.checked) {
      const uri = input.value
      const title = route.querySelector('input.route-title').value
      const icon = route.querySelector('input.folder-icon').value
      const hidden = route.querySelector('input.folder-hidden').value === 'true' ? true : false

      if (isMenu === false || hidden === false && isResolvableURI(uri))
        items.push({ uri, title, icon })
    }
  }

  return items
}

const isResolvableURI = (uri: string) => {
  const urisWithParams = uri.split('|').filter(uri => uri.includes('{'));
  const hasGetOrHead = uri.includes('@GET') || uri.includes('@HEAD');
  return urisWithParams.length === 0 && hasGetOrHead;
}

// The main RoutesTree component
const PrepareRoutesTreeDraggable: React.FC<Props> = ({ routes, permissions, allPermissions, handleSubmit, saving, savedFolders }) => {

  const [isInitialRender, setIsInitialRender] = useState(true);

  const [foldersCheckState, setFoldersCheckState] = useState<FolderCheck[]>([]);

  // Run checkbox checking logic once on component render
  useEffect(() => {
    if (isInitialRender) {
      setIsInitialRender(false); // Set isInitialRender to false after the initial render
    }
  }, []);

  const defaultList = routes;
  // React state to track order of items
  const [itemList, setItemList] = useState(defaultList);

  // Function to update list on drop
  const handleDrop = (droppedItem: any) => {
    // Ignore drop outside droppable container
    if (!droppedItem.destination) return;
    var updatedList = [...itemList];
    // Remove dragged item
    const [reorderedItem] = updatedList.splice(droppedItem.source.index, 1);
    // Add dropped item
    updatedList.splice(droppedItem.destination.index, 0, reorderedItem);
    // Update State
    setItemList(updatedList);
  };

  useEffect(() => {

    if (routes && !isInitialRender) {

      setTimeout(() => {

        const topLevelFoldersCheckBoxes = document.querySelectorAll(`div[id^="${PARENT_FOLDER_ID_PREFIX}"] input[id$="-parent-checkbox"][data-counter="1"]`);

        topLevelFoldersCheckBoxes.forEach((checkbox) => {

          const inputCheckbox = checkbox as HTMLInputElement;
          if (!inputCheckbox) return;

          if (!checkbox) return

          const parentDiv = inputCheckbox.closest(`div[id^="${PARENT_FOLDER_ID_PREFIX}"]`);

          if (!parentDiv) return

          const parentId = parentDiv.id;

          let checkedState: 'none' | 'checked' | 'indeterminate' = 'none';

          if (inputCheckbox.checked) {
            checkedState = 'checked';
          } else if (inputCheckbox.indeterminate) {
            checkedState = 'indeterminate';
          }

          const curr: FolderCheck = {
            parentId: Str.afterFirst(parentId, PARENT_FOLDER_ID_PREFIX),
            state: checkedState,
          };

          setFoldersCheckState((items) => {
            const updatedItems = items.map((item) =>
              item.parentId === curr.parentId ? curr : item
            );

            // If the current item doesn't exist, add it to the array
            if (!updatedItems.some((item) => item.parentId === curr.parentId)) {
              updatedItems.push(curr);
            }

            return updatedItems;
          });
        })

      }, 250)

    }

  }, [isInitialRender, routes, saving])

  return (
    <div>
      <form
        action=''
        onSubmit={(e: React.FormEvent) => {
          e.preventDefault();
          handleSubmit(constructMenus());
        }}
        className="w-100"
      >
        <div className='d-flex justify-content-end mt-2'>
          <button type="submit" className="btn btn-primary text-white" disabled={saving}>{saving ? 'Saving checked...' : 'Save checked'}</button>
        </div>
        <div className="d-flex align-items-start mt-1">
          <div className="nav flex-column nav-pills me-3 position-relative" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <DragDropContext onDragEnd={handleDrop}>
              <Droppable droppableId="list-container">
                {(provided: any) => (
                  <div
                    className="list-container"
                    {...provided.droppableProps}
                    ref={provided.innerRef}
                  >
                    {
                      itemList.map((child, i) => {

                        const folder = child.folder
                        const currentId = Str.slug((folder).replace(/^\//, ''));

                        const folderCheckState = foldersCheckState.find(item => item.parentId === currentId)

                        // console.log(foldersCheckState)

                        return <Draggable key={currentId} draggableId={currentId} index={i}>
                          {(provided: any) => (
                            <div
                              className="item-container ps-1 my-1 rounded text-dark d-flex gap-2 align-items-center border draggable"
                              ref={provided.innerRef}
                              {...provided.dragHandleProps}
                              {...provided.draggableProps}
                            >
                              <Icon icon="carbon:drag-vertical"></Icon>
                              <button className={`nav-link text-start border w-100 d-flex gap-1 align-items-center justify-content-between ${i === 0 ? 'show active' : ''}`} id={`v-pills-${currentId}-tab`} data-bs-toggle="pill" data-bs-target={`#v-pills-${currentId}`} type="button" role="tab" aria-controls={`v-pills-${currentId}`} aria-selected="true">
                                <div className="d-flex align-items-center gap-1">
                                  <span className={`text-primary bg-white rounded-circle d-flex align-items-center justify-content-center`} style={{ width: 20, height: 20 }}>
                                    {
                                      <Icon
                                        icon={
                                          !!folderCheckState && folderCheckState.state === 'checked'
                                            ? 'gg:check-o'
                                            : !!folderCheckState && folderCheckState.state === 'indeterminate'
                                              ? 'ri:indeterminate-circle-line'
                                              : 'ci:radio-unchecked'
                                        }
                                      />
                                    }
                                  </span>
                                  <span>{Str.title(folder)}</span>
                                </div>
                                {
                                  savedFolders.includes(folder)
                                    ?
                                    <span title="Saved!" className={`text-success bg-white rounded-circle d-flex align-items-center justify-content-center`} style={{ width: 20, height: 20 }}>
                                      {savedFolders.includes(folder) ? <><Icon icon="gg:check-o" /> </> : <> </>}
                                    </span>
                                    :
                                    ''
                                }
                              </button>
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

          <div className="w-100">
            <div className={`tab-content overflow-auto`} id="v-pills-tabContent">
              {
                routes.map((child, j) => {

                  const folder = child.folder
                  const currentId = Str.slug((folder).replace(/^\//, ''));

                  return <div key={`${currentId}`} className={`tab-pane fade ${j === 0 ? 'show active' : ''} ${MAIN_CONTAINER_CLASS} main-tree COUNTER0`} id={`v-pills-${currentId}`} role="tabpanel" aria-labelledby={`v-pills-${currentId}-tab`}>
                    <RoutesTree child={child} permissions={permissions} allPermissions={allPermissions} indent={0} counter={0} isInitialRender={isInitialRender} />
                  </div>
                }
                )
              }
            </div>
          </div>
        </div>
      </form>
    </div>
  );
}

export default PrepareRoutesTreeDraggable