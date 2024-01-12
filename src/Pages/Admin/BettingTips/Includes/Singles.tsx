import Loader from "@/components/Loader"
import useAxios from "@/hooks/useAxios"
import { useEffect, useState } from "react"
import Pagination from "@/components/Pagination"
import TipsContent from "./TipsContent"
import InvestmentCard from "./InvestmentCard"

type Props = {
    uri: string
    type: string
    odds_name: string
    odds_name_print: string
}

const Singles = ({ uri, type, odds_name, odds_name_print }: Props) => {

    const [page, setPage] = useState<number | string>(1);
    const [per_page, setPerPage] = useState<number | string>(35);
    const { data, get, loading } = useAxios()

    useEffect(() => {
        let localUri = `${uri}?type=${type}&page=${page}&per_page=${per_page}`
        get(localUri)
    }, [uri, type, page, per_page])

    return (
        <div>
            {
                data ?
                    <div className="card">
                        <div className="card-header">
                            <h5 className="d-flex gap-2 justify-content-between">Singles <span className="text-success">{data.total} tips</span></h5>
                        </div>
                        <div className="card-body">
                            {
                                !loading || data ?

                                    <>
                                        <TipsContent data={data.data} odds_name={odds_name} odds_name_print={odds_name_print} />
                                        <InvestmentCard investment={data.investment} />

                                    </>
                                    :
                                    <Loader />

                            }
                            <Pagination items={data} setPage={setPage} setPerPage={setPerPage} loading={loading} />
                        </div>

                    </div>
                    :
                    <>
                        {
                            loading ?
                                <Loader />
                                :
                                <div>Data Error</div>
                        }
                    </>

            }

        </div>
    )
}

export default Singles