import ProgressLine from '../ProgressLine/Index'

type Props = {
    totals: number
    data1: number
    data2: number
    data3?: number,
    data1ColorClass?: string
    data2ColorClass?: string
    data3ColorClass?: string
    label1?: string
    label2?: string
    label3?: string
}

const FormSummary = ({ totals, data1, data2, data3, data1ColorClass, data2ColorClass, data3ColorClass, label1, label2, label3 }: Props) => {

    const data1Percentage = ((data1 / totals) * 100).toFixed(0)
    const data2Percentage = ((data2 / totals) * 100).toFixed(0)

    let arr = [
        {
            percentage: `${data1Percentage}%${label1 || ''}`,
            colorClass: `${data1ColorClass || 'bg-success'}`
        },
        {
            percentage: `${data2Percentage}%${label2 || ''}`,
            colorClass: `${data2ColorClass || 'bg-warning'}`
        },
    ]

    let data3Percentage
    if (data3) {
        data3Percentage = ((data3 / totals) * 100).toFixed(0)

        arr.push({
            percentage: `${data3Percentage}%${label3 || ''}`,
            colorClass: `${data3ColorClass || 'bg-danger'}`
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