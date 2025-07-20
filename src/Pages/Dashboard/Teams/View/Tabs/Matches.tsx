import TeamMatchesCard from "@/components/Teams/TeamMatchesCard"
import useAxios from "@/hooks/useAxios"
import { GameInterface, TeamInterface } from "@/interfaces/FootballInterface"
import { useEffect, useState } from "react"

type Props = {
  record: TeamInterface | undefined
}

const Matches = ({ record }: Props) => {

  const team = record

  const { get: getTeamGames } = useAxios()

  const [teamGames, setTeamGames] = useState<GameInterface[]>()


  // Getting team games
  useEffect(() => {

    if (team) {
      getTeamGames(`dashboard/teams/view/${team.id}/matches?type=past&with_upcoming=1&before_to_date=1&per_page=15`).then((response) => {

        const results = response.results
        if (results) {
          setTeamGames(results.data)
        }
      })
    }
  }, [team])

  return (
    <div>
      {
        team &&
          <TeamMatchesCard team={team} teamGames={teamGames} />
      }

    </div>
  )
}

export default Matches