import { StandingInterface, StandingTableInterface } from '@/interfaces/FootballInterface'
import Composer from '@/utils/Composer'
import Str from '@/utils/Str'
import { NavLink } from 'react-router-dom'

type Props = {
  standings: StandingInterface[] | undefined
  minimal?: boolean
  homeTeamId?: string
  awayTeamId?: string
}

const StandingsTable = ({ standings, minimal, homeTeamId, awayTeamId }: Props) => {
  return (
    <div className='card'>
      {
        standings && standings.map((standing: StandingInterface) => {

          return (
            <>
              <div className="card-header">
                <div className="d-flex justify-content-between">
                  <p>{standing.stage}</p>
                  <p>{Str.before(standing.season.start_date, '-') + '/' + Str.before(standing.season.end_date, '-')}</p>
                </div>
                <div className="card-body standings">
                  <div className="team-standing row">
                    <div className="position col">
                      <div>
                        #
                      </div>
                    </div>
                    <div className={`team-name ${minimal ? 'col-7' : 'col-2'}`}>Team</div>
                    <div className="col played-games">MP</div>
                    {!minimal && (
                      <>
                        <div className="col won">W</div>
                        <div className="col draw">D</div>
                        <div className="col lost">L</div>
                        <div className="col goals-for">GF</div>
                        <div className="col goals-against">GA</div>
                        <div className="col goal-difference">GD</div>
                      </>
                    )}
                    <div className="col points">Pts</div>
                  </div>
                  <hr />
                </div>
                <div className="cursor-default striped-section">
                  {standing.standing_table.map((teamStanding: StandingTableInterface) => (
                    <NavLink key={teamStanding.id} to={`/admin/teams/view/${teamStanding.team.id}`} className={`text-decoration-none text-dark`}>
                      <div className={`team-standing row py-1 mb-1 rounded ${(teamStanding.team.id === homeTeamId || teamStanding.team.id === awayTeamId) ? 'bg-warning' : ''}`}>
                        <div className="position col">
                          <div className="p-1 bg-secondary rounded text-white text-center" style={{ width: 40 }}>
                            #{teamStanding.position}
                          </div>
                        </div>
                        <div className={`team-name ${minimal ? 'col-7' : 'col-2'} text-nowrap text-truncate`} title={Composer.team(teamStanding.team)}>{Composer.team(teamStanding.team)}</div>
                        <div className="played-games col">{teamStanding.played_games}</div>
                        {!minimal && (
                          <>
                            <div className="col won">{teamStanding.won}</div>
                            <div className="col draw">{teamStanding.draw}</div>
                            <div className="col lost">{teamStanding.lost}</div>
                            <div className="col goals-for">{teamStanding.goals_for}</div>
                            <div className="col goals-against">{teamStanding.goals_against}</div>
                            <div className="col goal-difference">{teamStanding.goal_difference}</div>
                          </>
                        )}
                        <div className="col points">{teamStanding.points}</div>
                      </div>
                    </NavLink>
                  ))}
                </div>
              </div>
            </>
          )

        })
      }
    </div>

  )
}

export default StandingsTable
