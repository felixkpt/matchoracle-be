import { GameInterface, TeamInterface } from "@/interfaces/FootballInterface";
import Composer from "@/utils/Composer";
import FormatDate from "@/utils/FormatDate";
import { NavLink } from "react-router-dom";
import RenderTeamLogoAndForm from "./RenderTeamLogoAndForm";
import { competitionLogo } from "@/utils/helpers";
import TimeAgo from "timeago-react";

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
          {RenderTeamLogoAndForm({ team: homeTeam, recentResults: homeTeamRecentResults })}
        </div>
      </div>
      <div className='col-12 col-md-6 col-lg-7 order-1 order-md-1 header-title shadow-sm border p-2 rounded mb-3 row text-center bg-light z-50'>
        <div className="competition-head d-flex flex-column align-items-center mb-2">
          <NavLink to={`/admin/competitions/view/${game.competition.id}`} className={`btn-link`}>
            <div className="p-1">
              {
                game.competition.logo &&
                <img className="compe-logo" src={competitionLogo(game.competition.logo)} alt="" />
              }
            </div>
            <div className="d-flex align-items-center gap-4">
              <h5 className="text-muted">{game.competition.country.name} - {game.competition.name}</h5>
            </div>
          </NavLink>
        </div>
        <div>
          <h4><NavLink to={`/admin/teams/view/${homeTeam.id}`} className="btn-link">{homeTeam.name}</NavLink> <span className="mx-2">vs</span> <NavLink to={`/admin/teams/view/${awayTeam.id}`} className="btn-link">{awayTeam.name}</NavLink></h4>
          <div className="row justify-content-evenly">
            <h6 className="col-md-12 col-xxl-6 text-muted">{FormatDate.toLocaleDateString(game?.utc_date)}</h6>
            <h6 className="col-md-12 col-xxl-6 text-muted">
              <div className="d-flex gap-2 justify-content-center justify-content-xxl-start">
                <span>Results:</span>
                <span className="d-flex gap-1">{
                  game.Winner == 'POSTPONED' ? 'PST' :
                    <>
                      FT: <b>{ftRes}</b> {htRes && <span>HT: <b>({htRes})</b></span>}
                    </>
                }
                </span>
              </div>
            </h6>
          </div>
          <div className="row justify-content-center">
            <small className="text-muted">Updated {<TimeAgo datetime={game.updated_at} />}</small>
          </div>
        </div>
      </div>
      <div className="col-6 col-md-3 col-lg-2 order-3 order-md-1 overflow-x-hidden">
        <div className="d-flex flex-column gap-3 align-items-center">
          {RenderTeamLogoAndForm({ team: awayTeam, recentResults: awayTeamRecentResults })}
        </div>
      </div>
    </div>
  )
}

export default MatchPageHeader