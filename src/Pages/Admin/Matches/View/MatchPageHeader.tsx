import LastXResultsIcons from "@/components/Teams/LastXResultsIcons";
import { GameInterface, TeamInterface } from "@/interfaces/FootballInterface";
import Composer from "@/utils/Composer";
import FormatDate from "@/utils/FormatDate";
import { NavLink } from "react-router-dom";

type Props = {
  game: GameInterface;
  homeTeam: TeamInterface
  awayTeam: TeamInterface
  homeTeamRecentResults: string[]
  awayTeamRecentResults: string[]
};

const MatchPageHeader = ({ game, homeTeam, awayTeam, homeTeamRecentResults, awayTeamRecentResults }: Props) => {

  const ftRes = Composer.results(game.score)
  const htRes = Composer.results(game.score, 'ht')
  return (
    <div className='header-title shadow-sm p-2 rounded mb-3 row justify-content-between'>
      <div className="col-6 col-md-3 col-lg-2 order-2 order-md-1 overflow-x-hidden">
        <div className="d-flex flex-column gap-3 align-items-center">
          <NavLink to={`/admin/teams/view/${homeTeam.id}`}>
            <img className='symbol-image-lg' src={homeTeam.crest} alt="" />
          </NavLink>
          <LastXResultsIcons results={homeTeamRecentResults} size="sm" />
        </div>
      </div>
      <div className='col-12 col-md-6 col-lg-7 order-1 order-md-1 header-title shadow-sm border p-2 rounded mb-3 row text-center bg-light z-50'>
        <div className="competition-head d-flex flex-column align-items-center">
          <div className="p-1 border rounded-circle">
            {
              game.competition.emblem &&
              <NavLink to={`/admin/competitions/view/${game.competition.id}`} className={`btn-link`}>
                <img className='symbol-image-md' src={game.competition.emblem} alt="" />
              </NavLink>
            }
          </div>
          <div className="d-flex align-items-center gap-4">
            <NavLink to={`/admin/competitions/view/${game.competition.id}`} className={`btn-link`}>
              <h5 className="text-muted">{game.competition.country.name} - {game.competition.name}</h5>
            </NavLink>
          </div>
        </div>
        <div>
          <h4><NavLink to={`/admin/teams/view/${homeTeam.id}`} className="btn-link">{homeTeam.name}</NavLink> <span className="mx-2">vs</span> <NavLink to={`/admin/teams/view/${awayTeam.id}`} className="btn-link">{awayTeam.name}</NavLink></h4>
          <div className="d-flex gap-2 justify-content-evenly"><h6 className="text-muted">{FormatDate.DDMMYYYY(game?.utc_date)}</h6>
            <h6 className="text-muted d-flex gap-2">
              <span>Results:</span> <span className="d-flex gap-1">FT: <b>{ftRes}</b> {htRes && <span>HT: <b>({htRes})</b></span>}</span>
            </h6>
          </div>
        </div>
      </div>
      <div className="col-6 col-md-3 col-lg-2 order-3 order-md-1 overflow-x-hidden">
        <div className="d-flex flex-column gap-3 align-items-center">
          <NavLink to={`/admin/teams/view/${awayTeam.id}`}>
            <img className='symbol-image-lg' src={awayTeam.crest} alt="" />
          </NavLink>
          <LastXResultsIcons results={awayTeamRecentResults} size="sm" />
        </div>
      </div>
    </div>
  )
}

export default MatchPageHeader