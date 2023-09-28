import React from 'react'

type Props = {
    routes:any,

}

const Selectors = ({routes, handleSubmit, constructMenus}: Props) => {
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
          <div className='d-flex justify-content-end'>
            <button type="submit" className="btn btn-primary text-white" disabled={saving}>{saving ? 'Saving checked...' : 'Save checked'}</button>
          </div>

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
        </form>
    </div>
  )
}

export default Selectors