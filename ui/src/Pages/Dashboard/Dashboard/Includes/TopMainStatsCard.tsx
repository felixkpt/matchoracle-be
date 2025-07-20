import { Icon } from '@iconify/react/dist/iconify.js'
import React from 'react'
import { NavLink } from 'react-router-dom'

interface Props {
    title: string
    icon: string
    link: string
    children: React.ReactNode

}
const TopMainStatsCard = ({ title, icon, link, children }: Props) => {

    return (
        <div className="col-lg-6 col-xl-4 mb-4">
            <NavLink to={link} className={'link-unstyled'}>
                <div className="card card-primary">
                    <div className="card-header">
                        <h5 className='d-flex align-items-center gap-1'>
                            <Icon width={'2rem'} icon={icon} />
                            <span>{title}</span>
                        </h5>
                    </div>
                    <div className="card-body text-center">
                        {children}
                    </div>
                </div>
            </NavLink>
        </div>
    )
}

export default TopMainStatsCard