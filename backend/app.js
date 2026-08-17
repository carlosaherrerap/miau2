import express from 'express';
import testRoutes from './src/routes/testRoutes.js';
import ticketRoutes from './src/routes/ticketRoutes.js'

const app=express();

app.use(express.json());
app.use('/api/test',testRoutes);
app.use('/api/tickets',ticketRoutes);

export default app;