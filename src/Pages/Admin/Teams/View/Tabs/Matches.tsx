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
      getTeamGames(`admin/teams/view/${team.id}/matches?type=played&per_page=15`).then((res) => {

        const { data: data1 } = res
        if (data1) {
          // get upcoming
          getTeamGames(`admin/teams/view/${team.id}/matches?type=upcoming&per_page=3`).then((res) => {

            const { data } = res
            if (data) {
              setTeamGames([...[].concat(data).reverse(), ...data1])

            } else {
              setTeamGames(data1)
            }
          })
        }
      })
    }
  }, [team])

  return (
    <div>
      {
        team &&
        <TeamMatchesCard team={team} teamGames={teamGames} context={'a'} />
      }

    </div>
  )
}

export default Matches