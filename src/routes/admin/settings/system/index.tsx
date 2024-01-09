
import joblogs from '@/routes/admin/settings/system/job-logs/index';
import predictionsPerformance from '@/routes/admin/settings/system/predictions-performance/index';

const index = [

  {
    path: 'job-logs',
    children: joblogs,
  },
  {
    path: 'predictions-performance',
    children: predictionsPerformance,
  },

]

export default index