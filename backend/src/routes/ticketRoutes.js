import Router from 'express';
import allTickets from '../controllers/TicketController.js';

const router=Router();

router.use('/',allTickets);

export default router;