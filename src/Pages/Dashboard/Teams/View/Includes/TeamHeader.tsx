import { TeamInterface } from "@/interfaces/FootballInterface"
import { renderCountryLogo, renderTeamLogo } from "@/utils/helpers"
import { NavLink } from "react-router-dom"

type TeamHeaderProps = {
    team: TeamInterface
    currentTab: string | undefined
}

const TeamHeader = ({ team, currentTab }: TeamHeaderProps) => {
    return (
        <div className='header-title shadow-sm p-2 rounded mb-3 row'>
            <div className="col-12 overflow-x-hidden">
                <div className="d-flex gap-3">
                    <img className="symbol-image-lg" src={renderTeamLogo(team.logo)} alt="" />
                    <div className="d-flex align-items-center gap-4">
                        <h5 className="row align-items-center gap-2">
                            <span><span>{team.name}</span><span>{currentTab ? ' - ' + currentTab : ''}</span></span>
                            <small className="d-flex align-items-center gap-2">
                                <NavLink to={`/dashboard/countries/view/${team.country.id}`} className="d-flex align-items-center btn-link">
                                    <img className="symbol-image-sm me-1" src={renderCountryLogo(team.country.flag)} alt="" />{team.country.name}
                                </NavLink>
                            </small>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default TeamHeader