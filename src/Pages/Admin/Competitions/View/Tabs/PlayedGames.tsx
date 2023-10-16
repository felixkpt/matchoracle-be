import { CompetitionInterface } from '@/interfaces/CompetitionInterface'

interface Props {

  record: CompetitionInterface | undefined
}

const PlayedGames = ({ record }: Props) => {
  const competition = record

  return (
    <div>PlayedGames</div>
  )
}

export default PlayedGames