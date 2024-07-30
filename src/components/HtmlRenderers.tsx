import { TeamInterface } from "@/interfaces/FootballInterface"
import { scores } from "@/utils/constants"
import { renderDefaultImage } from "@/utils/helpers"
import { NavLink } from "react-router-dom"
import TimeAgo from "timeago-react"

export function resolveURI(uri: string) {
  return uri.replace(/\/+/g, '/')
}

export const renderTableId = (value: string, uri: string) => {
  return (
    <a className="dropdown-item autotable-view hover-underline text-decoration-underline" data-id={value} href={resolveURI(`${uri}/view/${value}`)}>#{value}</a>
  )
}

export const renderTime = (value: string) => {
  return <TimeAgo datetime={value} />
}

export const renderImage = (value: string) => {
  return <img src={renderDefaultImage(value)} width={30} height={30} className='rounded shadow' />
}

export const renderMatchViewlink = (value: string) => {
  return (
    <NavLink className="hover-underline text-dark" data-id={value} to={`/dashboard/matches/view/${value}`}>#{value}</NavLink>
  )
}

export const renderGameViewLink = (_: string, row: any) => {
  const id = row.id
  const home_team: TeamInterface = row.home_team
  const away_team: TeamInterface = row.away_team

  return (
    <NavLink className="hover-underline text-dark" data-id={id} to={`/dashboard/matches/view/${id}`}>{home_team.name} vs {away_team.name}</NavLink>
  )
}

export const renderHT1X2 = (_: string, row: any) => {
  const className = 'border-start text-dark';

  const pred = row.prediction_strategy

  let str: string = '-';
  if (pred && pred.ht_home_win_proba) {
    str = pred.ht_home_win_proba + '%, ' + pred.ht_draw_proba + '%, ' + pred.ht_away_win_proba + '%';
  }

  return (
    <div className={`border-4 ps-1 text-nowrap ${className} d-inline-block`}>{str}</div>
  );

}

export const renderHT1X2Pick = (_: string, row: any) => {

  let className = 'bg-light-blue text-dark';

  const pred = row.prediction_strategy;
  let str: string = '-';
  if (pred && pred.ht_home_win_proba) {
    str = pred.ht_hda_pick == 0 ? '1' : (pred.ht_hda_pick == 1 ? 'X' : '2');
    const has_res = row.hasResultsHT;
    const res = row.winningSideHT;

    if (has_res) {
      if (pred.ht_hda_pick == res) {
        className = 'border-bottom bg-success text-white';
      } else if (pred) {
        className = 'border-bottom border-danger text-danger';
      }
    }
  }

  return (
    <div className={`rounded-circle border p-1 ${className} d-inline-block text-center results-icon-md`}>{str}</div>
  )

}

export const renderFT1X2 = (_: string, row: any) => {
  const className = 'border-start text-dark';
  const pred = row.prediction_strategy
  let str: string = '-';

  if (pred && pred.ft_home_win_proba) {
    str = pred.ft_home_win_proba + '%, ' + pred.ft_draw_proba + '%, ' + pred.ft_away_win_proba + '%';
  }

  return (
    <div className={`border-4 ps-1 text-nowrap ${className} d-inline-block`}>{str}</div>
  );

}

export const renderFT1X2Pick = (_: string, row: any) => {

  let className = 'bg-light-blue text-dark';

  const pred = row.prediction_strategy;

  let str: string = '-';
  if (pred && pred.ft_home_win_proba) {
    str = pred.ft_hda_pick == 0 ? '1' : (pred.ft_hda_pick == 1 ? 'X' : '2');
    const has_res = row.hasResultsFT;
    const res = row.winningSideFT;

    if (has_res) {
      if (pred.ft_hda_pick == res) {
        className = 'border-bottom bg-success text-white';
      } else if (pred) {
        className = 'border-bottom border-danger text-danger';
      }
    }
  }

  return (
    <div className={`rounded-circle border p-1 ${className} d-inline-block text-center results-icon-md`}>{str}</div>
  )

}

export const renderBTS = (_: string, row: any) => {

  let className = 'border-bottom-light-blue text-dark';
  const pred = row.prediction_strategy
  let str: string = '-';

  if (pred) {
    str = pred.bts_pick == 1 ? 'YES' : 'NO';
    const has_res = row.hasResults;
    const res = row.BTS;

    if (pred.bts_pick == res) {
      className = 'border-bottom border-success';
    } else if (pred && has_res) {
      className = 'border-bottom border-danger';
    }
  }

  return (
    <div className={`border-2 py-1 ${className} d-inline-block text-center results-icon-md`}>{str}</div>
  )
}

export const renderOver25 = (_: string, row: any) => {

  let className = 'border-bottom-light-blue text-dark';
  const pred = row.prediction_strategy
  let str: string = '-';

  if (pred) {
    str = pred.over_under25_pick == 1 ? 'OV' : 'UN';
    const has_res = row.hasResultsFT;
    const res = row.goalsCount;

    if (has_res) {
      if (pred.over_under25_pick == 1 && res > 2) {
        className = 'border-bottom border-success';
      } else if (pred.over_under25_pick == 0 && res <= 2) {
        className = 'border-bottom border-success';
      } else {
        className = 'border-bottom border-danger';
      }
    }
  }

  return (
    <div className={`border-2 py-1 ${className} d-inline-block text-center results-icon-md`}>{str}</div>
  )
}

export const renderCS = (_: string, row: any) => {

  let className = 'border-bottom-light-blue text-dark';
  const pred = row.prediction_strategy
  let str: string = '-';

  if (pred) {

    const has_res = row.hasResults;
    const res = row.CS;

    if (has_res && res) {
      className = 'border-bottom border-success';
    }

    const obj: { [key: string]: number } = scores()

    let cs;
    for (const key in obj) {
      const element = obj[key];
      if (pred.cs == element) {
        cs = key
        break
      }
    }

    if (cs) {
      str = cs;
    }
  }

  return (
    <div className={`border-2 py-0 text-nowrap ${className}`}>{str}</div>
  );
}

export const formatHTScores = (_: string, row: any) => {

  const className = 'border-start text-dark';

  return <div className={`scores-sec border-4 p-1 text-nowrap ${className} d-inline-block text-center results-icon-md`}>{row.half_time}</div>
}

export const formatFTScores = (_: string, row: any) => {

  const className = 'border-start text-dark';

  return <div className={`scores-sec border-4 p-1 text-nowrap ${className} d-inline-block text-center results-icon-md`}>{row.full_time}</div>
}

export const UTCDate = (_: string, row: any) => {
  return <span className="text-nowrap"><TimeAgo datetime={row.utc_date} /></span>
}