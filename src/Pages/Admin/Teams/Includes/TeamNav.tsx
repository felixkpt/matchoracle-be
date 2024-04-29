import { NavLink } from 'react-router-dom'

const TeamsNav = () => {
    return (
        <nav className="navbar navbar-expand-lg navbar-light bg-light">
            <div className="container-fluid">
                <div className="collapse navbar-collapse" id="navbarNav">
                    <ul className="navbar-nav">
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/admin/teams/club-teams">Club teams</NavLink>
                        </li>
                        <li className="nav-item">
                            <NavLink className="nav-link" to="/admin/teams/national-teams">National teams</NavLink>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    )
}

export default TeamsNav