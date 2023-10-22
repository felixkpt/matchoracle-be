import Loader from '@/components/Loader'
import StandingsTable from '@/components/StandingsTable'
import TeamMatchesCard from '@/components/TeamMatchesCard'
import useAxios from '@/hooks/useAxios'
import { GameInterface, StandingInterface, TeamInterface } from '@/interfaces/FootballInterface'
import FormatDate from '@/utils/FormatDate'
import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'

const Index = () => {

  const { get: getGameDetails } = useAxios()
  const { id } = useParams()

  const [game, setGame] = useState<GameInterface>()
  const [homeTeam, setHomeTeam] = useState<TeamInterface>()
  const [awayTeam, setAwayTeam] = useState<TeamInterface>()

  const { get: getHomeTeamGames } = useAxios()
  const { get: getAwayTeamGames } = useAxios()
  const [homeTeamGames, setHomeTeamGames] = useState<GameInterface[]>()
  const [awayTeamGames, setAwayTeamGames] = useState<GameInterface[]>()

  const { get: getStandings } = useAxios()
  const [standings, setStandings] = useState<StandingInterface[]>()

  // Getting game details
  useEffect(() => {

    if (id) {
      setHomeTeamGames(undefined)
      setAwayTeamGames(undefined)

      getGameDetails(`admin/matches/view/${id}`).then((res) => {
        const { data } = res
        if (data) {
          setGame(data)
          setHomeTeam(data.home_team)
          setAwayTeam(data.away_team)
        }
      })
    }
  }, [id])

  // Getting homeTeam games
  useEffect(() => {

    if (homeTeam) {
      getHomeTeamGames(`admin/teams/view/${homeTeam.id}/matches?type=played&per_page=15`).then((res) => {

        const { data } = res
        if (data) {
          setHomeTeamGames(data)
        }
      })
    }
  }, [homeTeam])


  // Getting awayTeam games
  useEffect(() => {

    if (awayTeam) {
      getAwayTeamGames(`admin/teams/view/${awayTeam.id}/matches?type=played&per_page=15`).then((res) => {

        const { data } = res
        if (data) {
          setAwayTeamGames(data)
        }
      })
    }
  }, [awayTeam])

  // Getting standings
  useEffect(() => {

    if (game) {
      getStandings(`admin/competitions/view/${game.competition_id}/standings/${game.season_id}`).then((res) => {

        const { standings } = res

        if (standings) {
          setStandings(standings)
        }
      })
    }
  }, [game])

  return (
    <div>
      {
        homeTeam && awayTeam ?
          <div className=''>
            <h4>Match Preview for {homeTeam.name} vs {awayTeam.name} - {FormatDate.DDMMYYYY(game?.utc_date)}</h4>

            <div className="row">
              <div className="col-12 col-md-9">
                <div className="card">
                  <div className="card-body">
                    <div className="row">
                      <div className="col-12"><h5 className='rounded text-center'>Past matches</h5></div>
                      {/* Home Card */}
                      <div className='col-6'>
                        <TeamMatchesCard team={homeTeam} teamGames={homeTeamGames} context={'h'} />
                      </div>
                      {/* Away Card */}
                      <div className='col-6'>
                        <TeamMatchesCard team={awayTeam} teamGames={awayTeamGames} context={'a'} />
                      </div>

                    </div>
                  </div>
                </div>
              </div>
              <div className="col-12 col-md-3">
                <StandingsTable standings={standings} minimal={true} homeTeamId={homeTeam.id} awayTeamId={awayTeam.id} />
              </div>
            </div>
          </div>
          :
          <div className='position-relative'><Loader message='Loading' /></div>
      }
    </div>
  )
}

export default Index