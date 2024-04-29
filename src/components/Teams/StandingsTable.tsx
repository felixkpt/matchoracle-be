import { StandingInterface, StandingTableInterface } from '@/interfaces/FootballInterface'
import Composer from '@/utils/Composer'
import Str from '@/utils/Str'
import { teamLogo } from '@/utils/helpers'
import { NavLink } from 'react-router-dom'
import TimeAgo from 'timeago-react'
import Loader from '../Loader'

type Props = {
  standings: StandingInterface[] | undefined
  minimal?: boolean
  homeTeamId?: string
  awayTeamId?: string
}

const StandingsTable = ({ standings, minimal, homeTeamId, awayTeamId }: Props) => {

  return (
    <div className="card">
      <div className="card-body">
        <h6>Standings</h6>
        {
          standings ?
            standings.map((standing: StandingInterface) => {
              const table_name = standing.stage || standing.group || standing.type
              return (
                <div key={standing.id} className='card mb-4' id={`standings-table${table_name ? '-' + Str.slug(table_name) : ''}`}>
                  <div className="card-header p-0">
                    <div className="d-flex justify-content-between align-items-center p-2 text-muted">
                      <p>{table_name}</p>
                      <p>{Str.before(standing.season.start_date, '-') + '/' + Str.before(standing.season.end_date, '-')}</p>
                    </div>
                    <div className="card-body standings">
                      <div className="team-standing d-flex">
                        <div className="position col">
                          <div>
                            #
                          </div>
                        </div>
                        <div className={`team-name ${minimal ? 'col-6 col-md-4 col-lg-5' : 'col-2'}`}>Team</div>
                        <div className="col points"><span className="standings-sortable">Pts</span></div>
                        <div className="col played-games"><span className="standings-sortable">MP</span></div>
                        {!minimal && (
                          <>
                            <div className="col won"><span className="standings-sortable">W</span></div>
                            <div className="col draw"><span className="standings-sortable">D</span></div>
                            <div className="col lost"><span className="standings-sortable">L</span></div>
                            <div className="col goals-for"><span className="standings-sortable">GF</span></div>
                            <div className="col goals-against"><span className="standings-sortable">GA</span></div>
                            <div className="col goal-difference"><span className="standings-sortable">GD</span></div>
                          </>
                        )}
                      </div>
                      <hr />
                    </div>

                  </div>
                  <div className="card-body">
                    <div className="cursor-default striped-section">
                      {standing.standing_table.map((teamStanding: StandingTableInterface) => (
                        <NavLink key={teamStanding.id} to={`/admin/teams/view/${teamStanding.team.id}`} className={`text-decoration-none text-dark`}>
                          <div className={`team-standing d-flex align-items-center py-1 mb-1 rounded ${(teamStanding.team.id === homeTeamId || teamStanding.team.id === awayTeamId) ? 'bg-warning' : ''}`}>
                            <div className="position col">
                              <div className="p-1 bg-secondary rounded text-white text-center" style={{ width: 40 }}>
                                #{teamStanding.position}
                              </div>
                            </div>
                            <div className={`team-name ${minimal ? 'col-6 col-md-4 col-lg-6' : 'col-2'} text-nowrap text-truncate`} title={Composer.team(teamStanding.team)}>
                              <span className='d-flex gap-1 align-items-center'>
                                <img className='symbol-image-xm' src={teamLogo(teamStanding.team.logo)} alt="" />
                                <span className='text-truncate'>{Composer.team(teamStanding.team)}</span>
                              </span>
                            </div>
                            <div className="col points">{teamStanding.points}</div>
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
                          </div>
                        </NavLink>
                      ))}
                    </div>
                    <div className='d-flex'>
                      <small className={`col-12 text-muted ${minimal ? 'text-center' : 'text-center text-md-end'}`}>Updated {<TimeAgo datetime={standing.updated_at} />}</small>
                    </div>
                  </div>
                </div>
              )
            })
            :
            <Loader justify='start' />
        }
      </div>
    </div>

  )
}

export default StandingsTable
