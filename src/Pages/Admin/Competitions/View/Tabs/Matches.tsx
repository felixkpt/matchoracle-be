import Loader from '@/components/Loader';
import useAxios from '@/hooks/useAxios';
import { CompetitionInterface } from '@/interfaces/CompetitionInterface'
import { useEffect } from 'react';

interface Props {

  record: CompetitionInterface | undefined
}

const Matches = ({ record }: Props) => {
  const competition = record
  const { get, loading } = useAxios<CompetitionInterface>();
  const { loading: loadingMatches, post } = useAxios<CompetitionInterface>();

  useEffect(() => {

    if (competition) {
      get(`admin/competitions/view/${competition.id}/matches`).then((res) => {
        console.log(res)
      })
    }

  }, [competition])

  const fetchMatches = () => {
    if (competition) {
      post(`admin/competitions/view/${competition.id}/fetch-matches`).then((res) => {
        if (res) {
          console.log(333)
        }
      })
    }
  }

  return (
    <div>
      <div className='d-flex justify-content-between position-relative'>

        <h4>Matches</h4>
        {loadingMatches ? <><Loader message='Fetching' /></> : ''}
        <button className='btn btn-sm btn-success' onClick={fetchMatches}>Fetch Matches</button>
      </div>


    </div>
  )
}

export default Matches