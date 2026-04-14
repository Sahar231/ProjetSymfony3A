package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Resultat;
import edu.connexion3a36.services.ResultatService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.stage.Stage;

import java.io.IOException;
import java.sql.SQLException;

public class ListeResultatController {

    @FXML
    private TableView<Resultat> resultatTable;

    @FXML
    private TableColumn<Resultat, Integer> colId;

    @FXML
    private TableColumn<Resultat, Double> colScore;

    @FXML
    private TableColumn<Resultat, String> colCreatedAt;

    private final ResultatService resultatService = new ResultatService();
    private ObservableList<Resultat> masterData = FXCollections.observableArrayList();

    @FXML
    private javafx.scene.control.TextField searchTF;

    @FXML
    public void initialize() {
        colId.setCellValueFactory(new PropertyValueFactory<>("idResultat"));
        colScore.setCellValueFactory(new PropertyValueFactory<>("score"));
        colCreatedAt.setCellValueFactory(new PropertyValueFactory<>("createdAt"));
        chargerResultats();
    }

    public void chargerResultats() {
        try {
            masterData = FXCollections.observableArrayList(resultatService.afficherResultat());
            resultatTable.setItems(masterData);
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors du chargement des résultats : " + e.getMessage());
        }
    }

    @FXML
    private void handleSearch(javafx.event.ActionEvent event) {
        String q = searchTF.getText() == null ? "" : searchTF.getText().trim().toLowerCase();
        if (q.isEmpty()) { resultatTable.setItems(masterData); return; }
        ObservableList<Resultat> filtered = FXCollections.observableArrayList();
        for (Resultat r : masterData) {
            boolean matches = false;
            if (String.valueOf(r.getIdResultat()).contains(q)) matches = true;
            if (!matches && String.valueOf(r.getScore()).contains(q)) matches = true;
            if (!matches && r.getCreatedAt() != null && r.getCreatedAt().toString().toLowerCase().contains(q)) matches = true;
            if (matches) filtered.add(r);
        }
        resultatTable.setItems(filtered);
    }

    public Resultat getSelectedResultat() {
        return resultatTable.getSelectionModel().getSelectedItem();
    }

    @FXML
    private void handleAdd(ActionEvent event) { openFXML(event, "/resultat_add.fxml"); }

    @FXML
    private void handleEdit(ActionEvent event) {
        if (getSelectedResultat() == null) { ControllerUtils.showWarning("Sélectionnez un résultat à modifier."); return; }
        openFXML(event, "/resultat_edit.fxml");
    }

    @FXML
    private void handleDelete(ActionEvent event) {
        if (getSelectedResultat() == null) { ControllerUtils.showWarning("Sélectionnez un résultat à supprimer."); return; }
        javafx.scene.control.Alert confirm = new Alert(Alert.AlertType.CONFIRMATION, "Supprimer le résultat sélectionné ?", javafx.scene.control.ButtonType.YES, javafx.scene.control.ButtonType.NO);
        confirm.showAndWait();
        if (confirm.getResult() == javafx.scene.control.ButtonType.YES) {
            try {
                resultatService.supprimerResultat(getSelectedResultat().getIdResultat());
                ControllerUtils.showInfo("Résultat supprimé.");
                chargerResultats();
            } catch (SQLException e) {
                ControllerUtils.showError("Erreur lors de la suppression : " + e.getMessage());
            }
        }
    }

    @FXML
    private void handleRefresh(ActionEvent event) { chargerResultats(); }

    @FXML
    private void handleBack(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/home.fxml"));
            Parent root = loader.load();
            Node source = (Node) event.getSource();
            Stage stage = (Stage) source.getScene().getWindow();
            Scene scene = stage.getScene();
            if (scene == null) {
                Scene newScene = new Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (IOException e) {
            ControllerUtils.showError("Impossible de retourner à l'accueil: " + e.getMessage());
        }
    }

    private void openFXML(ActionEvent event, String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Object controller = loader.getController();
            try {
                if (controller != null) {
                    java.lang.reflect.Method m = null;
                    try { m = controller.getClass().getMethod("setResultat", Resultat.class); } catch (NoSuchMethodException ignored) {}
                    if (m != null) m.invoke(controller, getSelectedResultat());
                }
            } catch (Exception ignored) {}
            Stage stage = (Stage) ((Node) event.getSource()).getScene().getWindow();
            Scene scene = stage.getScene();
            if (scene == null) {
                Scene newScene = new Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (IOException e) {
            ControllerUtils.showError("Impossible d'ouvrir la page: " + e.getMessage());
        }
    }
}
