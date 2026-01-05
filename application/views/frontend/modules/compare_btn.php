<style>
    #compare-floating-btn {
        position: fixed; bottom: 200px; right: 20px; width: 60px; height: 60px;
        background: linear-gradient(135deg, #ffa500, #ff8c00);
        border-radius: 50%; box-shadow: 0 4px 15px rgba(255, 165, 0, 0.4);
        cursor: pointer; z-index: 99999; display: flex; justify-content: center; align-items: center;
        animation: pulse 2s infinite;
    }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(255, 165, 0, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0); } }

    #compare-floating-btn i { color: white; font-size: 22px; }

</style>

<a href="<?php echo base_url('so-sanh'); ?>" id="compare-floating-btn" title="So sánh sản phẩm">
    <i class="fas fa-balance-scale"></i>
</a>