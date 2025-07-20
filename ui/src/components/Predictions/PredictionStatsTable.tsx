import React, { useEffect, useState } from 'react';
import { PredictionCategoryInterface, PredictionSummaryType } from '@/interfaces/FootballInterface';
import useAxios from '@/hooks/useAxios';
import Loader from '../Loader';
import Str from '@/utils/Str';

interface Props {
  baseUri: string;
}

const renderCategory = (category: string, data: PredictionCategoryInterface) => {
  return (
    <tr key={category}>
      <td>{Str.title(category.replace(/_/g, ' '), { gg: 'GG', ng: 'NG' })}</td>
      <td>{data.counts}</td>
      <td>{data.preds}</td>
      <td>{data.preds_true}</td>
      <td>{data.preds_true_percentage}%</td>
    </tr>
  );
};

const renderNestedCategories = (categories: PredictionSummaryType) => {
  return Object.entries(categories).map(([subcategory, data]) => {
    if (subcategory === 'counts') return null;
    return renderCategory(subcategory, data);
  });
};

const PredictionStatsTable: React.FC<Props> = ({ baseUri }) => {
  const { get, loading } = useAxios();
  const [FTStats, setFTStats] = useState<PredictionSummaryType | null>(null);
  const [HTStats, setHTStats] = useState<PredictionSummaryType | null>(null);

  useEffect(() => {
    if (baseUri) {
      get(baseUri, { params: { get_prediction_stats: true } }).then((response) => {
        if (response.results) {
          const data = response.results;
          const { ft, ht } = data;

          const ft_counts = ft.counts;
          setFTStats({ ...ft, counts: ft_counts });

          const ht_counts = ht.counts;
          setHTStats({ ...ht, counts: ht_counts });
        }
      });
    }
  }, [baseUri]);

  // Function to calculate totals
  const calculateTotals = (stats: PredictionSummaryType) => {
    const totals = {
      counts: 0,
      preds: 0,
      preds_true: 0,
      preds_true_percentage: 0,
    };

    Object.values(stats).forEach((categoryData) => {
      totals.counts += categoryData.counts || 0;
      totals.preds += categoryData.preds || 0;
      totals.preds_true += categoryData.preds_true || 0;
    });

    if (totals.preds > 0) {
      totals.preds_true_percentage = (totals.preds_true / totals.preds) * 100;
    }

    return totals;
  };

  // Render the totals row
  const renderTotals = (stats: PredictionSummaryType) => {
    const totals = calculateTotals(stats);

    return (
      <tr key="totals" className="fw-bold">
        <td>Totals/Average Score</td>
        <td>{totals.counts}</td>
        <td>{totals.preds}</td>
        <td>{totals.preds_true}</td>
        <td>{totals.preds_true_percentage.toFixed(2)}%</td>
      </tr>
    );
  };

  return (
    <div className="container mt-5 pb-4">
      <div className="card">
        <div className="card-header">
          <h4 className="mb-4">Prediction Statistics</h4>
        </div>
        <div className="card-body overflow-auto">
          {FTStats ? (
            <div>
              <div className="mb-4">
                <h5>Fulltime stats {FTStats ? ' for ' + FTStats.counts + ' games' : ''}</h5>
                <table className="table table-bordered table-striped">
                  <thead className="thead-dark">
                    <tr>
                      <th>Category</th>
                      <th>Occurency</th>
                      <th>Predictions</th>
                      <th>True Predictions</th>
                      <th>True Percentage</th>
                    </tr>
                  </thead>
                  <tbody>
                    {FTStats && renderNestedCategories(FTStats)}
                    {FTStats && renderTotals(FTStats)}
                  </tbody>
                </table>
              </div>
              <div className="mb-4">
                <h5>Halftime stats {HTStats ? ' for ' + HTStats.counts + ' games' : ''}</h5>
                <table className="table table-bordered table-striped">
                  <thead className="thead-dark">
                    <tr>
                      <th>Category</th>
                      <th>Occurency</th>
                      <th>Predictions</th>
                      <th>True Predictions</th>
                      <th>True Percentage</th>
                    </tr>
                  </thead>
                  <tbody>
                    {HTStats && renderNestedCategories(HTStats)}
                    {HTStats && renderTotals(HTStats)}
                  </tbody>
                </table>
              </div>
            </div>
          ) : (
            <>
              {loading ? <Loader justify="center" /> : <div>No data available</div>}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default PredictionStatsTable;
