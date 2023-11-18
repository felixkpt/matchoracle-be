import ProgressLine from '../ProgressLine/Index'

type Props = {
    totals: number
    teamWins: number
    draws: number
    teamLoses?: number,
    winColorClass?: string
    drawColorClass?: string
    loseColorClass?: string
    labelA?: string
    labelB?: string
    labelC?: string
}

const FormSummary = ({ totals, teamWins, draws, teamLoses, winColorClass, drawColorClass, loseColorClass, labelA, labelB, labelC }: Props) => {

    const teamWinsPercentage = ((teamWins / totals) * 100).toFixed(0)
    const drawsPercentage = ((draws / totals) * 100).toFixed(0)

    let arr = [
        {
            percentage: `${teamWinsPercentage}%${labelA || ''}`,
            colorClass: `${winColorClass || 'bg-success'}`
        },
        {
            percentage: `${drawsPercentage}%${labelB || ''}`,
            colorClass: `${drawColorClass || 'bg-warning'}`
        },
    ]

    let teamLosesPercentage
    if (teamLoses) {
        teamLosesPercentage = ((teamLoses / totals) * 100).toFixed(0)

        arr.push({
            percentage: `${teamLosesPercentage}%${labelC || ''}`,
            colorClass: `${loseColorClass || 'bg-danger'}`
        }
        )
    }

    return (
        <div>
            <ProgressLine
                visualParts={arr}
            />
        </div>)
}

export default FormSummary