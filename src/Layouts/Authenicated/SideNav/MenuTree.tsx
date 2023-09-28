import Str from '@/utils/Str';
import { Icon } from '@iconify/react/dist/iconify.js';
import React from 'react'
import RoutesList from './RoutesList';

type Props = {
    child: RoutesSection
    indent: number
    prevId: string
}

// The main RoutesTree component
const MenuTree: React.FC<Props> = ({ child, indent, prevId }) => {

    indent += 1

    const { routes, children, hidden, icon, folder } = child

    const shouldShowFolder = routes.length > 0 || children.length > 0 && !hidden

    const currentId = Str.slug((folder).replace(/^\//, ''));

    return (

        <>
            {
                shouldShowFolder &&

                <div key={currentId}>
                    <a className="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target={`#${currentId}`} aria-expanded="false" aria-controls="pagesCollapseAuth">
                        <span className='d-flex align-items-center gap-1'>
                            <Icon icon={`${icon || 'prime:bookmark'}`} />
                            <span>{Str.title(Str.afterLast(folder, '/'))}</span>
                        </span>
                        <div className="sb-sidenav-collapse-arrow"><Icon className='arrow-section' icon={`bi-chevron-down`} /></div>
                    </a>
                    <div className="collapse" id={`${currentId}`} aria-labelledby="headingOne" data-bs-parent={`#${prevId}`}>
                        <nav className="sb-sidenav-menu-nested nav">
                            <RoutesList routes={routes} />
                            {
                                children.length > 0 &&
                                <>
                                    {
                                        children.map((child) => <MenuTree key={child.folder} indent={indent} child={child} prevId={currentId} />)
                                    }
                                </>
                            }
                        </nav>
                    </div>
                </div>
            }
        </>
    )
}

export default MenuTree