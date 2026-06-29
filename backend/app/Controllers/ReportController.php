<?php
/**
 * Report Controller - Owner-scoped analytics
 */
namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;

class ReportController
{
    public function index(array $params = []): void
    {
        $ownerId = Router::getAuthUserId();
        $db = Database::getInstance();

        // Revenue by month (last 12 months)
        $monthlyRevenue = $db->fetchAll(
            "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total
             FROM payments 
             WHERE owner_id = ? AND status = 'completed'
             AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(date, '%Y-%m')
             ORDER BY month ASC",
            [$ownerId]
        );

        // Property performance
        $propertyPerformance = $db->fetchAll(
            "SELECT p.id, p.name, p.type, p.units, p.occupied,
                    COALESCE(SUM(pay.amount), 0) as collected,
                    COALESCE(SUM(b.total), 0) as expected
             FROM properties p
             LEFT JOIN houses h ON p.id = h.property_id
             LEFT JOIN bills b ON h.id = b.house_id AND b.month = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
             LEFT JOIN payments pay ON h.id = pay.house_id AND pay.status = 'completed' AND DATE_FORMAT(pay.date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
             WHERE p.owner_id = ?
             GROUP BY p.id, p.name, p.type, p.units, p.occupied
             ORDER BY p.name",
            [$ownerId]
        );

        // Collection rate
        $totalExpected = array_sum(array_column($propertyPerformance, 'expected'));
        $totalCollected = array_sum(array_column($propertyPerformance, 'collected'));
        $collectionRate = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0;

        // Occupancy stats
        $totalUnits = $db->fetchOne(
            "SELECT COALESCE(SUM(units), 0) as total FROM properties WHERE owner_id = ?",
            [$ownerId]
        );
        $occupiedUnits = $db->fetchOne(
            "SELECT COUNT(*) as total FROM houses WHERE owner_id = ? AND status = 'occupied'",
            [$ownerId]
        );

        Router::jsonResponse([
            'monthly_revenue'    => $monthlyRevenue,
            'property_performance' => $propertyPerformance,
            'collection_rate'    => $collectionRate,
            'total_revenue_ytd'  => array_sum(array_column($monthlyRevenue, 'total')),
            'total_units'        => (int) ($totalUnits['total'] ?? 0),
            'occupied_units'     => (int) ($occupiedUnits['total'] ?? 0),
            'occupancy_rate'     => ($totalUnits['total'] ?? 0) > 0 ? round(($occupiedUnits['total'] ?? 0) / ($totalUnits['total'] ?? 0) * 100, 1) : 0,
        ]);
    }
}