import { useEffect, useState } from "react";
import useAxios from "./useAxios";

const useGetAbout = () => {
    const { get: getAbout, loading, loaded, errors } = useAxios()

    const [data, setData] = useState(null);
    useEffect(() => {
        getAbout('/about/view/default').then((resp) => {
            if (resp.results) {
                setData(resp.results.data)
            }
        })
    }, [])

    return { data, loading, loaded, errors }
}

export default useGetAbout