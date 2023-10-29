import ProgressLine from '../ProgressLine/Index'

type Props = {
    totals: number
    teamWins: number
    draws: number
    teamLoses: number,
    winColorClass?: string
    drawColorClass?: string
    loseColorClass?: string
}

const FormSummary = ({ totals, teamWins, draws, teamLoses, winColorClass, drawColorClass, loseColorClass }: Props) => {

    const teamWinsPercentage = ((teamWins / totals) * 100).toFixed(0)
    const drawsPercentage = ((draws / totals) * 100).toFixed(0)
    const teamLosesPercentage = ((teamLoses / totals) * 100).toFixed(0)

    return (
        <div>
            <ProgressLine
                visualParts={[
                    {
                        percentage: `${teamWinsPercentage}%`,
                        colorClass: `${winColorClass || 'bg-success'}`
                    },
                    {
                        percentage: `${drawsPercentage}%`,
                        colorClass: `${drawColorClass || 'bg-warning'}`
                    },
                    {
                        percentage: `${teamLosesPercentage}%`,
                        colorClass: `${loseColorClass || 'bg-danger'}`
                    }
                ]}
            />
        </div>)
}

export default FormSummary