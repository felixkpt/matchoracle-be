import React, { useEffect, useState } from 'react';
import { PredictionCategoryInterface, PredictionStatisticsInterface } from '@/interfaces/FootballInterface';
import useAxios from '@/hooks/useAxios';
import Loader from '../Loader';
import Str from '@/utils/Str';

interface Props {
  baseUri: string;
}

const renderCategory = (category: string, data: any) => {
  return (
    <tr key={category}>
      <td>{Str.title(category.replace(/_/g, ' '), {gg: 'GG', ng:'NG'})}</td>
      <td>{data.counts}</td>
      <td>{data.preds}</td>
      <td>{data.preds_true}</td>
      <td>{data.preds_true_percentage}%</td>
    </tr>
  );
};

const renderNestedCategories = (categories: any) => {
  return Object.entries(categories).map(([subcategory, data]) => {
    return renderCategory(subcategory, data);
  });
};

const PredictionStatsTable: React.FC<Props> = ({ baseUri }) => {

  const { get, loading } = useAxios()
  const [FTStats, setFTStats] = useState<Partial<PredictionStatisticsInterface> | null>(null)
  const [HTStats, setHTStats] = useState<Partial<PredictionStatisticsInterface> | null>(null)

  useEffect(() => {
    if (baseUri) {
      get(baseUri, { params: { get_prediction_stats: true } }).then((res) => {
        if (res) {
          console.log(res)
          const { ft, ht, counts, average_score } = res;
          setFTStats({ ft, counts, average_score });
          
          const ht_counts = ht.counts
          setHTStats({ ht, counts:ht_counts, average_score });
        }
      });
    }
  }, [baseUri]);

  // Function to calculate totals
  const calculateTotals = (stats: any) => {
    const totals = {
      counts: 0,
      preds: 0,
      preds_true: 0,
      preds_true_percentage: 0,
    };

    // Check if stats is not null before proceeding
    if (stats) {
      // Iterate through categories
      Object.values(stats).forEach((cData) => {

        const categoryData = cData as PredictionCategoryInterface

        // Sum up relevant values
        totals.counts += categoryData.counts || 0;
        totals.preds += categoryData.preds || 0;
        totals.preds_true += categoryData.preds_true || 0;
      });

      // Calculate true percentage based on the total predictions and true predictions
      totals.preds_true_percentage = totals.preds_true / totals.preds || 0;
    }

    return totals;
  };


  // Render the totals row
  const renderTotals = (stats: PredictionCategoryInterface) => {
    const totals = calculateTotals(stats);

    return (
      <tr key="average_score" className='fw-bold'>
        <td>Totals/Average Score</td>
        <td>{totals.counts}</td>
        <td>{totals.preds}</td>
        <td>{totals.preds_true}</td>
        <td>{(totals.preds_true_percentage * 100).toFixed(2)}%</td>
      </tr>
    );
  };

  return (
    <div className="container mt-5 pb-4">
      <div className="card">
        <div className="card-header">
          <h3 className="mb-4">Prediction Statistics</h3>
        </div>
        <div className="card-body">
          {FTStats ? (
            <div>
              <div className='mb-4'>
                <h5>Fulltime stats {FTStats ? ' for ' + FTStats.counts + ' games' : ''}</h5>
                <table className="table table-bordered table-striped">
                  <thead className="thead-dark">
                    <tr>
                      <th>Category</th>
                      <th>Counts</th>
                      <th>Predictions</th>
                      <th>True Predictions</th>
                      <th>True Percentage</th>
                    </tr>
                  </thead>
                  <tbody>
                    {renderNestedCategories(FTStats.ft)}
                    {
                      FTStats.ft
                      &&
                      renderTotals(FTStats.ft)
                    }
                  </tbody>
                </table>
              </div>
              <div className='mb-4'>
                <h4>Halftime stats {HTStats ? ' for ' + HTStats.counts + ' games' : ''}</h4>
                <table className="table table-bordered table-striped">
                  <thead className="thead-dark">
                    <tr>
                      <th>Category</th>
                      <th>Counts</th>
                      <th>Predictions</th>
                      <th>True Predictions</th>
                      <th>True Percentage</th>
                    </tr>
                  </thead>
                  <tbody>
                    {renderNestedCategories(HTStats?.ht)}
                    {HTStats?.ht && renderTotals(HTStats.ht)}
                  </tbody>
                </table>
              </div>
            </div>
          ) : (
            <>
              {
                loading ?
                  <Loader justify='center' />
                  :
                  <div>No data available</div>
              }
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default PredictionStatsTable;
