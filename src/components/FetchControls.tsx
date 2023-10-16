import TimeAgo from 'timeago-react';

type Props = {
    item: any,
    limitList: number[];
    setLimit: any;
}

function FetchControls({ item, limitList, setLimit }: Props) {
    return (
        <div className="row">
            <div className="col-5 font-weight-bold">Last fetch date
                {item.last_fetch ?
                    <TimeAgo datetime={item.last_fetch} locale='en' />
                    : 'never'}
            </div>
            <div className="col-7 row">
                <div className="col-7 d-flex gap-1 align-items-center">
                    <span className="col-6">Stop at:</span>
                    <span className="col-6">
                        <select className="form-control" onChange={(e: any) => setLimit(e.target.value)}>
                            {limitList && limitList.map((number) =>
                                <option value={number} key={number}>{number}</option>
                            )}
                        </select>
                    </span>
                </div>
                <div className="col-5">
                    <button className="btn btn-primary" type="submit">Fetch now!</button>
                </div>
            </div>
        </div>

    )
}

export default FetchControls