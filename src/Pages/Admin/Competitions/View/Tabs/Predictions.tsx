import { CompetitionInterface } from "@/interfaces/CompetitionInterface"

type Props = {
    record: CompetitionInterface | undefined
}

const Predictions = ({ record }: Props) => {
    const competition = record

    return (
        <div>Predictions</div>
    )
}

export default Predictions