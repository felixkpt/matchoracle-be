import Loader from '@/components/Loader'
import useAxios from '@/hooks/useAxios'
import { GameInterface, StandingInterface, TeamInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import LastMatches from './LastMatches'
import StandingsTable from '@/components/Teams/StandingsTable'
import MatchPageHeader from './MatchPageHeader'
import VotesSection from './VotesSection'
import Head2HeadCard from '@/components/Teams/Head2HeadCard'
import PredictionsSection from './PredictionsSection'

const Index = () => {

  const { get: getGameDetails } = useAxios()
  const { id } = useParams()

  const [game, setGame] = useState<GameInterface>()
  const [homeTeam, setHomeTeam] = useState<TeamInterface>()
  const [awayTeam, setAwayTeam] = useState<TeamInterface>()
  const [homeTeamRecentResults, setHomeTeamRecentResults] = useState<string[]>(['U', 'U', 'U', 'U', 'U'])
  const [awayTeamRecentResults, setAwayTeamRecentResults] = useState<string[]>(['U', 'U', 'U', 'U', 'U'])

  const { get: getStandings } = useAxios()
  const [standings, setStandings] = useState<StandingInterface[]>()

  // Getting game details
  useEffect(() => {

    if (id) {
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
        game && homeTeam && awayTeam ?
          <div className=''>
            <div className="row">
              <div className="col-12 col-xl-9">
                <MatchPageHeader game={game} homeTeam={homeTeam} awayTeam={awayTeam} homeTeamRecentResults={homeTeamRecentResults} awayTeamRecentResults={awayTeamRecentResults} />
                <VotesSection game={game} />
                <div className="row">
                  <div className="col-12">
                    <LastMatches game={game} homeTeam={homeTeam} awayTeam={awayTeam} perPage={8} />
                  </div>
                  <div className="col-12 col-xl-8">
                    <LastMatches game={game} homeTeam={homeTeam} awayTeam={awayTeam} currentground={true} perPage={5} setHomeTeamRecentResults={setHomeTeamRecentResults} setAwayTeamRecentResults={setAwayTeamRecentResults} />
                  </div>
                </div>
              </div>
              <div className="col-12 col-xl-3">
                <div className='mb-5'>
                  <StandingsTable standings={standings} minimal={true} homeTeamId={homeTeam.id} awayTeamId={awayTeam.id} />
                </div>
                <div className='mb-5'>
                  <Head2HeadCard key={game.id} game={game} homeTeam={homeTeam} perPage={5} awayTeam={awayTeam} />
                </div>
              </div>
              <div className="col-12">
                <PredictionsSection game={game} />
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