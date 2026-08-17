import Router from 'express';
import getTickets from '../controllers/TestController.js';

const router=Router();

router.use('/', getTickets);

export default router;