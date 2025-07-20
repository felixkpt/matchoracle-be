import ResultsIcon from './ResultsIcon'

type Props = {
  results: string[]
  size?: 'sm' | 'md'
}

const LastXResultsIcons = ({ results, size }: Props) => {
  return (
    <div className='d-flex gap-1'>
      {
        results && results.map((result, i) =>
          <div key={i}>
            <ResultsIcon winner={result} size={size} />
          </div>
        )
      }
    </div>
  )
}

export default LastXResultsIcons